<?php

namespace FluentMail\App\Services\Mailer\Providers\Simulator;

use FluentMail\App\Services\Mailer\BaseHandler;

class Handler extends BaseHandler
{
    public function send()
    {

        return true;
    }

    protected function postSend()
    {
        $returnResponse = [
            'response' => 'OK'
        ];

        $this->response = $returnResponse;
        return $this->handleResponse($this->response);
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }
}
