<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class OAuth extends BaseConfig
{
    public array $providers = [
        'google' => [
            'clientId'     => env('OAUTH_GOOGLE_CLIENT_ID'),
            'clientSecret' => env('OAUTH_GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => env('OAUTH_GOOGLE_REDIRECT_URI'),
            'scopes'       => ['openid', 'email', 'profile'],
        ],
    ];
}
