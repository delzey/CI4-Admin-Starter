<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class JWT extends BaseConfig
{
    public string $algo = 'RS256';

    // private/public key paths (or inline contents)
    public string $privateKey = WRITEPATH . 'keys/jwt_private.pem';
    public string $publicKey  = WRITEPATH . 'keys/jwt_public.pem';

    // seconds
    public int $accessTokenTTL  = 900;      // 15 min
    public int $refreshTokenTTL = 2592000;  // 30 days

    // issuer / audience
    public string $issuer   = 'ci4-starter';
    public ?string $audience = null; // or e.g. 'https://api.example.com'
}