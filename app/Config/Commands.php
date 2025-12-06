<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Commands extends BaseConfig
{
    public $commands = [
        'db:dump' => \App\Commands\DbDump::class,
    ];
}
