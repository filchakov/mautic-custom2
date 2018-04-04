<?php

namespace App\Providers\Mautic;

use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

class MauticService
{

    protected $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param string $name
     * @return \Mautic\Api\Api
     */
    public function getClient($name = 'emails'){

        $settings = [
            'userName'   => $this->config['login'],
            'password'   => $this->config['password'],
            'debug' => true
        ];

        $initAuth = new ApiAuth();

        $auth = $initAuth->newAuth($settings, 'BasicAuth');

        $api = new MauticApi();

        return $api->newApi($name, $auth, env('MAUTIC_URL'));

    }

    /**
     * @return array
     */
    public function getTagsList(){
        $tags = [
            9 => 'job_seeker',
            8 => 'member',
        ];

        return $tags;
    }
}