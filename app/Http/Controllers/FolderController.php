<?php

namespace FluentMail\App\Http\Controllers;

use FluentMail\App\Models\Folder;
use FluentMail\App\Models\Logger;
use FluentMail\App\Models\Settings;
use FluentMail\Includes\Request\Request;

class FolderController extends Controller
{

    public function index(Request $request, Folder $folder)
    {
        $this->verify();
        return $this->send(
            $folder->get([
                'user_id' => get_current_user_id()
            ])
        );
    }

    public function get(Request $request, Logger $logger)
    {
        $this->verify();

        return $this->send(
            $logger->get(
                $request->except(['nonce', 'action'])
            )
        );
    }

    public function show(Request $request, Logger $logger)
    {
        $this->verify();

        $result = $logger->navigate($request->all());

        return $this->sendSuccess($result);
    }

    public function delete(Request $request, Logger $logger)
    {
        $this->verify();

        $id = (array) $request->get('id');

        $logger->delete($id);

        if ($id && $id[0] == 'all') {
            $subject = 'All logs';
        } else {
            $count = count($id);
            $subject = $count > 1 ? "{$count} Logs" : 'Log';
        }

        return $this->sendSuccess([
            'message' => "{$subject} deleted successfully."
        ]);
    }

    public function retry(Request $request, Logger $logger)
    {
        $this->verify();

        try {
            $this->app->addAction('wp_mail_failed', function ($response) use ($logger, $request) {
                $log = $logger->find($id = $request->get('id'));
                $log['retries'] = $log['retries'] + 1;
                $logger->updateLog($log, ['id' => $id]);

                $this->sendError([
                    'message' => $response->get_error_message(),
                    'errors' => $response->get_error_data()
                ], $response->get_error_code());
            });

            if ($email = $logger->resendEmailFromLog($request->get('id'), $request->get('type'))) {
                return $this->sendSuccess([
                    'email' => $email,
                    'message' => __('Email sent successfully.', 'fluent-smtp')
                ]);
            }

            throw new \Exception('Something went wrong', 400);
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function retryBulk(Request $request, Logger $logger)
    {
        $this->verify();
        $logIds = $request->get('log_ids', []);

        $failedCount = 0;
        $this->app->addAction('wp_mail_failed', function ($response) use (&$failedCount) {
            $failedCount++;
        });

        $failedInitiated = 0;
        $successCount = 0;
        foreach ($logIds as $logId) {
            try {
                $email = $logger->resendEmailFromLog($logId, 'check_realtime');
                $successCount++;
            } catch (\Exception $exception) {
                $failedInitiated++;
            }
        }
        $message = 'Selected Emails have been proceed to send.';

        if ($failedCount) {
            $message .= ' But ' . $failedCount . ' emails are reported to failed to send.';
        }

        if ($failedInitiated) {
            $message .= ' And ' . $failedInitiated . ' emails are failed to init the emails';
        }

        return $this->sendSuccess([
            'message' => $message
        ]);
    }

    public function store(Request $request, Folder $folder)
    {
        $this->verify();

        $name = sanitize_text_field($request->get('name'));

        if (empty($name) || strlen($name) < 3 || strlen($name) > 100) {
            return $this->sendError([
                'message' => __('Please provide a valid Folder Name. It should be between 3 to 100 characters.', 'fluent-smtp')
            ]);
        }

        $data = [
            'name' => $name,
            'user_id' => get_current_user_id()
        ];

        $result = $folder->add($data);

        if (is_wp_error($result)) {
            return $this->sendError([
                'message' => $result->get_error_message()
            ]);
        }

        return $this->sendSuccess([
            'message' => __('New folder createed successfully ', 'fluent-smtp')
        ]);
    }
}
