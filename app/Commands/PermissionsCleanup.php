<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\AuthGroups;

/**
 * Removes invalid user permissions (those not defined in Config\AuthGroups)
 *
 * Usage:
 *   php spark permissions:cleanup
 *   php spark permissions:cleanup --force
 */
class PermissionsCleanup extends BaseCommand
{
    protected $group       = 'Shield';
    protected $name        = 'permissions:cleanup';
    protected $description = 'Removes invalid entries from auth_permissions_users.';
    protected $usage       = 'permissions:cleanup [--force]';
    protected $options     = [
        '--force' => 'Actually delete invalid permissions instead of just previewing them.'
    ];

    public function run(array $params)
    {
        $force = CLI::getOption('force');

        $db = db_connect();
        $tables = array_map('strtolower', $db->listTables());
        if (! in_array('auth_permissions_users', $tables)) {
            CLI::write('❌  Table auth_permissions_users not found.', 'red');
            return;
        }

        $config = new AuthGroups();
        $validPermissions = array_keys($config->permissions ?? []);

        if (empty($validPermissions)) {
            CLI::write('⚠️  No valid permissions defined in Config\\AuthGroups.php', 'yellow');
            return;
        }

        $query = $db->table('auth_permissions_users')->select('id, user_id, permission')->get();
        $invalid = [];

        foreach ($query->getResultArray() as $row) {
            if (! in_array($row['permission'], $validPermissions, true)) {
                $invalid[] = $row;
            }
        }

        if (empty($invalid)) {
            CLI::write('✅  No invalid permissions found.', 'green');
            return;
        }

        CLI::newLine();
        CLI::write('⚠️  Invalid permissions detected:', 'yellow');
        CLI::newLine();

        $header = sprintf("%-5s | %-10s | %-30s", 'ID', 'User ID', 'Invalid Permission');
        CLI::write($header, 'cyan');
        CLI::write(str_repeat('-', strlen($header)), 'cyan');

        foreach ($invalid as $row) {
            CLI::write(sprintf("%-5d | %-10d | %-30s", $row['id'], $row['user_id'], $row['permission']));
        }

        CLI::newLine();
        CLI::write('⚠️  Total invalid: ' . count($invalid), 'yellow');

        if (! $force) {
            CLI::newLine();
            CLI::write('🟡  Preview mode only. Use --force to actually delete these entries.', 'yellow');
            return;
        }

        // Delete invalid entries
        $ids = array_column($invalid, 'id');
        $db->table('auth_permissions_users')->whereIn('id', $ids)->delete();

        CLI::newLine();
        CLI::write('✅  Removed ' . count($ids) . ' invalid entries from auth_permissions_users.', 'green');
    }
}
