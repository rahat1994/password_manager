<?php

namespace FluentMail\App\Hooks\Handlers;

use FluentMail\App\Services\NotificationHelper;
use FluentMail\Includes\Support\Arr;

class SchedulerHandler
{
    protected $dailyActionName = 'fluentmail_do_daily_scheduled_tasks';

    public function register()
    {
        add_action($this->dailyActionName, array($this, 'handleScheduledJobs'));
        add_action('fluentmail_email_sending_failed', array($this, 'maybeHandleFallbackConnection'), 10, 3);

        add_action('fluentsmtp_renew_gmail_token', array($this, 'renewGmailToken'));

        add_action('fluentmail_email_sending_failed_no_fallback', array($this, 'maybeSendNotification'), 10, 3);
    }

    public function handleScheduledJobs()
    {
        $this->deleteOldEmails();
        $this->sendDailyDigest();
    }

    private function deleteOldEmails()
    {
    }

    public function sendDailyDigest()
    {
    }

    private function getDomainName()
    {
        $parts = parse_url(site_url());
        $url = $parts['host'] . (isset($parts['path']) ? $parts['path'] : '');
        return untrailingslashit($url);
    }

    public function maybeHandleFallbackConnection($logId, $handler, $data = [])
    {
        if (defined('FLUENTMAIL_EMAIL_TESTING')) {
            return false;
        }

        $fallbackConnectionId = \FluentMail\Includes\Support\Arr::get($settings, 'misc.fallback_connection');

        if (!$fallbackConnectionId) {
            do_action('fluentmail_email_sending_failed_no_fallback', $logId, $handler, $data);
            return false;
        }

        $fallbackConnection = \FluentMail\Includes\Support\Arr::get($settings, 'connections.' . $fallbackConnectionId);

        if (!$fallbackConnection) {
            do_action('fluentmail_email_sending_failed_no_fallback', $logId, $handler, $data);
            return false;
        }

        $phpMailer = $handler->getPhpMailer();

        $fallbackSettings = $fallbackConnection['provider_settings'];
        $phpMailer->setFrom($fallbackSettings['sender_email'], $phpMailer->FromName);

        // Trap the fluentSMTPMail mailer here
        $phpMailer = new \FluentMail\App\Services\Mailer\FluentPHPMailer($phpMailer);
        return $phpMailer->sendViaFallback($logId);
    }

    public function renewGmailToken()
    {
        $settings = fluentMailGetSettings();

        if (!$settings) {
            return;
        }

        $connections = Arr::get($settings, 'connections', []);

        foreach ($connections as $connection) {
            if (Arr::get($connection, 'provider_settings.provider') != 'gmail') {
                continue;
            }
            $providerSettings = $connection['provider_settings'];
            if (($providerSettings['expire_stamp'] - 480) < time() && !empty($providerSettings['refresh_token'])) {
                $this->callGmailApiForNewToken($connection['provider_settings']);
            }
        }
    }

    public function callGmailApiForNewToken($settings)
    {
        if (Arr::get($settings, 'key_store') == 'wp_config') {
            $settings['client_id'] = defined('FLUENTMAIL_GMAIL_CLIENT_ID') ? FLUENTMAIL_GMAIL_CLIENT_ID : '';
            $settings['client_secret'] = defined('FLUENTMAIL_GMAIL_CLIENT_SECRET') ? FLUENTMAIL_GMAIL_CLIENT_SECRET : '';
        }

        if (!class_exists('\FluentSmtpLib\Google\Client')) {
            require_once FLUENTMAIL_PLUGIN_PATH . 'includes/libs/google-api-client/build/vendor/autoload.php';
        }

        try {
            $client = new \FluentSmtpLib\Google\Client();
            $client->setClientId($settings['client_id']);
            $client->setClientSecret($settings['client_secret']);
            $client->addScope("https://www.googleapis.com/auth/gmail.compose");
            $client->setAccessType('offline');
            $client->setApprovalPrompt('force');

            $tokens = [
                'access_token'  => $settings['access_token'],
                'refresh_token' => $settings['refresh_token'],
                'expires_in'    => $settings['expire_stamp'] - time()
            ];

            $client->setAccessToken($tokens);

            $newTokens = $client->refreshToken($tokens['refresh_token']);
            $result = $this->saveNewGmailTokens($settings, $newTokens);

            if (!$result) {
                return new \WP_Error('api_error', 'Failed to renew the token');
            }

            return true;
        } catch (\Exception $exception) {
            return new \WP_Error('api_error', $exception->getMessage());
        }
    }


    public function maybeSendNotification($rowId, $handler, $logData = [])
    {
        $channel = NotificationHelper::getActiveChannelSettings();

        if (!$channel) {
            return false;
        }

        $lastNotificationSent = get_option('_fsmtp_last_notification_sent');
        if ($lastNotificationSent && (time() - $lastNotificationSent) < 60) {
            return false;
        }

        update_option('_fsmtp_last_notification_sent', time());

        $driver = $channel['driver'];
        if ($driver == 'telegram') {
            $data = [
                'token_id'      => $channel['token'],
                'provider'      => $handler->getSetting('provider'),
                'error_message' => $this->getErrorMessageFromResponse(maybe_unserialize(Arr::get($logData, 'response')))
            ];

            return NotificationHelper::sendFailedNotificationTele($data);
        }

        if ($driver == 'slack') {
            return NotificationHelper::sendSlackMessage(NotificationHelper::formatSlackMessageBlock($handler, $logData), $channel['webhook_url'], false);
        }

        if ($driver == 'discord') {
            return NotificationHelper::sendDiscordMessage(NotificationHelper::formatDiscordMessageBlock($handler, $logData), $channel['webhook_url'], false);
        }

        return false;
    }

    private function saveNewGmailTokens($existingData, $tokens)
    {
        if (empty($tokens['access_token']) || empty($tokens['refresh_token'])) {
            return false;
        }

        $senderEmail = $existingData['sender_email'];

        $existingData['access_token'] = $tokens['access_token'];
        $existingData['refresh_token'] = $tokens['refresh_token'];
        $existingData['expire_stamp'] = $tokens['expires_in'] + time();
        $existingData['expires_in'] = $tokens['expires_in'];

        (new Settings())->updateConnection($senderEmail, $existingData);
        fluentMailGetProvider($senderEmail, true); // we are clearing the static cache here
        wp_schedule_single_event($existingData['expire_stamp'] - 360, 'fluentsmtp_renew_gmail_token');
        return true;
    }

    private function getErrorMessageFromResponse($response)
    {
        if (!$response || !is_array($response)) {
            return '';
        }

        if (!empty($response['fallback_response']['message'])) {
            $message = $response['fallback_response']['message'];
        } else {
            $message = Arr::get($response, 'message');
        }

        if (!$message) {
            return '';
        }

        if (!is_string($message)) {
            $message = json_encode($message);
        }

        return $message;
    }
}
