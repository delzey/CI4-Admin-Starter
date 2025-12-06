<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MenuCacheClear extends BaseCommand
{
    protected $group       = 'Menu';
    protected $name        = 'menu:cache:clear';
    protected $description = 'Clears the cached sidebar menu tree.';

    public function run(array $params)
    {
        cache()->delete('sidebar_menu_tree');
        CLI::write('✅ Sidebar menu cache cleared.', 'green');
    }
}
