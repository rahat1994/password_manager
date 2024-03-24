<?php

namespace FluentMail\App\Http\Controllers;

use Exception;
use FluentMail\App\Models\Folder;
use FluentMail\App\Models\Logger;
use FluentMail\App\Models\Item;
use FluentMail\App\Services\EncryptAuthenticationWrapper;
use FluentMail\Includes\Request\Request;
use FluentSmtpLib\Google\Auth\Cache\Item as CacheItem;

class ItemController extends Controller
{

    public function index(Request $request, Item $item)
    {
        $this->verify();
        return $this->send(
            $item->get(
                array_merge(
                    $request->except(['nonce', 'action']),
                    ['user_id' => get_current_user_id()]
                )
            )
        );
    }

    public function get(Request $request, Item $item)
    {
        $this->verify();

        return $this->send(
            $item->get(
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

    public function store(Request $request,  Item $item, Folder $folder)
    {
        $this->verify();

        $itemType = $request->get('item_type');

        $validItemTypes = ['login', 'card', 'identity', 'securenote'];
        if (!in_array($itemType, $validItemTypes)) {
            return $this->sendError([
                'message' => __('Invalid item type.', 'fluent-smtp')
            ]);
        }

        $folderId = $request->get('folder');
        // get all the folders from this user
        $folders = $folder->get([
            'user_id' => get_current_user_id()
        ])['data'];

        if (empty($folders)) {
            return $this->sendError([
                'message' => __('Please create a folder first.', 'fluent-smtp')
            ]);
        }

        // check if the folder exists
        if (!in_array($folderId, array_column($folders, 'id'))) {
            return $this->sendError([
                'message' => __('Invalid folder.', 'fluent-smtp')
            ]);
        }

        $name = sanitize_text_field($request->get('name'));
        $username = sanitize_text_field($request->get('username'));
        $password = sanitize_text_field($request->get('password'));
        $url = esc_url_raw($request->get('url')) === $request->get('url') ? $request->get('url') : '';
        $desc = sanitize_text_field($request->get('desc'));
        $masterPassProtected = $request->get('delivery') ? true : false;

        if (empty($name) || strlen($name) < 3 || strlen($name) > 100) {
            return $this->sendError([
                'message' => __('Please provide a valid Folder Name. It should be between 3 to 100 characters.', 'fluent-smtp')
            ]);
        }

        if (empty($username) || strlen($username) < 3 || strlen($username) > 100) {
            return $this->sendError([
                'message' => __('Please provide a valid Username. It should be between 3 to 100 characters.', 'fluent-smtp')
            ]);
        }

        if (empty($password) || strlen($password) < 8 || strlen($password) > 100) {
            return $this->sendError([
                'message' => __('Please provide a valid Password. It should be between 8 to 100 characters.', 'fluent-smtp')
            ]);
        }

        if (empty($url)) {
            return $this->sendError([
                'message' => __('Please provide a valid URL.', 'fluent-smtp')
            ]);
        }

        if (empty($desc) || strlen($desc) < 3 || strlen($desc) > 500) {
            return $this->sendError([
                'message' => __('Please provide a valid Description. It should be between 3 to 500 characters.', 'fluent-smtp')
            ]);
        }

        if (!is_bool($masterPassProtected)) {
            return $this->sendError([
                'message' => __('Please provide a valid Delivery option.', 'fluent-smtp')
            ]);
        }
        $encryptionData = $this->encryptPass($password);

        // echo '<pre>';
        // print_r($encryptionData);
        // exit;
        if (is_wp_error($encryptionData)) {
            return $this->sendError([
                'message' => $encryptionData->get_error_message()
            ]);
        }

        // print_r($encryptionData);
        $data = [
            'name' => $name,
            'username' => $username,
            'password' => $encryptionData['password'],
            'key' => base64_encode($encryptionData['key']),
            'login_url' => $url,
            'note' => $desc,
            'folder_id' => $folderId,
            'collection_id' => 1,
            'organization_id' => 1,
            'master_pass_secured' => $masterPassProtected,
            'user_id' => get_current_user_id()
        ];


        $result = $item->add($data);

        if (is_wp_error($result) || $result == 0) {

            if ($result == 0) {
                return $this->sendError([
                    'message' => __('Something went wrong.', 'fluent-smtp')
                ]);
            }

            return $this->sendError([
                'message' => $result->get_error_message()
            ]);
        }

        if ($result instanceof Exception) {
            return $this->sendError([
                'message' => $result->getMessage()
            ]);
        }

        return $this->sendSuccess([
            'message' => __('New Item createed successfully ', 'fluent-smtp')
        ]);
    }

    public function encryptPass($password)
    {
        $key = EncryptAuthenticationWrapper::generateKey();
        $encryptedPassword = EncryptAuthenticationWrapper::encrypt($password, $key);

        return [
            'key' => $key,
            'password' => $encryptedPassword
        ];
    }
}
