<?php

namespace FluentMail\App\Http\Controllers;

use FluentMail\App\Models\Folder;
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
            'message' => __('New folder created successfully ', 'fluent-smtp')
        ]);
    }
}
