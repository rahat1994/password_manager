<?php

namespace FluentMail\App\Http\Controllers;

use Exception;
use FluentMail\App\Models\Collection;
use FluentMail\App\Models\Folder;
use FluentMail\App\Models\Logger;
use FluentMail\App\Models\Item;
use FluentMail\App\Models\Organization;
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

    public function dataSanitize(Request $request, Item $item, Folder $folder)
    {

        $itemType = $request->get('itemType');

        $validItemTypes = ['login', 'card', 'identity', 'secure_note'];
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

        if (is_wp_error($encryptionData)) {
            return $this->sendError([
                'message' => $encryptionData->get_error_message()
            ]);
        }

        return [
            'name' => $name,
            'username' => $username,
            'password' => $encryptionData['password'],
            'key' => base64_encode($encryptionData['key']),
            'login_url' => $url,
            'note' => $desc,
            'folder_id' => $folderId,
            'master_pass_secured' => $masterPassProtected,
            'user_id' => get_current_user_id()
        ];
    }

    public function store(Request $request,  Item $item, Folder $folder)
    {
        $this->verify();

        $data = $this->dataSanitize($request, $item, $folder);

        $organization = new Organization();

        // add default Organization if not exists
        $user_organizations = $organization->get([
            'user_id' => get_current_user_id()
        ]);

        if (empty($user_organizations['data'])) {
            // get current user email address
            $user = get_userdata(get_current_user_id());
            $organizationData = [
                'name' => 'Default',
                'billing_email' => $user->user_email,
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ];

            $organization_id = $organization->add($organizationData);

            if (is_wp_error($organization_id)) {
                return $this->sendError([
                    'message' => $organization_id->get_error_message()
                ]);
            }
        } else {
            $organization_id = $user_organizations['data'][0]['id'];
        }

        $collection = new Collection();
        // add default collection if not exists
        $user_collections = $collection->get([
            'user_id' => get_current_user_id()
        ]);

        if (empty($user_collections['data'])) {
            $collectionData = [
                'name' => 'Default',
                'user_id' => get_current_user_id(),
                'organization_id' => $organization_id,
                'created_at' => current_time('mysql')
            ];

            $collection_id = $collection->add($collectionData);

            if (is_wp_error($collection_id)) {
                return $this->sendError([
                    'message' => $collection_id->get_error_message()
                ]);
            }
        } else {
            $collection_id = $user_collections['data'][0]['id'];
        }

        $data = array_merge($data, [
            'collection_id' => $collection_id,
            'organization_id' => $organization_id
        ]);
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // die();
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

    public function update(Request $request, Item $item, Folder $folder)
    {
        $this->verify();

        $itemId = $request->get('item_id');
        $data = $request->except(['nonce', 'action']);

        // sanitize the data
        $data = $this->dataSanitize($request, $item, $folder);

        $result = $item->update($itemId, $data);

        if (is_wp_error($result)) {
            return $this->sendError([
                'message' => $result->get_error_message()
            ]);
        }

        return $this->sendSuccess([
            'message' => __('Item updated successfully.', 'fluent-smtp')
        ]);
    }
}
