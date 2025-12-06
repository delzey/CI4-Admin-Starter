<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Menu extends BaseConfig
{
    public bool $cacheEnabled = true;
    public int  $cacheTTL     = 3600;
}
