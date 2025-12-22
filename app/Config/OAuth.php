<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class OAuth extends BaseConfig
{
    /**
     * Provider configuration.
     *
     * We keep the property simple and fill it in the constructor so we can
     * safely call env() without hitting "constant expression" errors.
     */
    public array $providers = [];

    public function __construct()
    {
        parent::__construct();

        $this->providers = [
            'google' => [
                'clientId'     => env('OAUTH_GOOGLE_CLIENT_ID'),
                'clientSecret' => env('OAUTH_GOOGLE_CLIENT_SECRET'),
                'redirectUri'  => env('OAUTH_GOOGLE_REDIRECT_URI'),
                'scopes'       => ['openid', 'email', 'profile'],
            ],

            // placeholders for future providers:
            'facebook' => [
                'clientId'     => env('OAUTH_FACEBOOK_CLIENT_ID'),
                'clientSecret' => env('OAUTH_FACEBOOK_CLIENT_SECRET'),
                'redirectUri'  => env('OAUTH_FACEBOOK_REDIRECT_URI'),
                'scopes'       => ['email'],
            ],

            'github' => [
                'clientId'     => env('OAUTH_GITHUB_CLIENT_ID'),
                'clientSecret' => env('OAUTH_GITHUB_CLIENT_SECRET'),
                'redirectUri'  => env('OAUTH_GITHUB_REDIRECT_URI'),
                'scopes'       => ['user:email'],
            ],

            'apple' => [
                'clientId'     => env('OAUTH_APPLE_CLIENT_ID'),
                'teamId'       => env('OAUTH_APPLE_TEAM_ID'),
                'keyFile'      => env('OAUTH_APPLE_KEY_FILE'),
                'keyId'        => env('OAUTH_APPLE_KEY_ID'),
                'redirectUri'  => env('OAUTH_APPLE_REDIRECT_URI'),
                'scopes'       => ['name', 'email'],
            ],
        ];
    }
}
