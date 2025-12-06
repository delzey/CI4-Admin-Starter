<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;
use Config\AuthGroups;

/**
 * Permissions maintenance utilities for CodeIgniter Shield.
 *
 * Usage:
 *   php spark permissions:list
 *   php spark permissions:audit
 */
class Permissions extends BaseCommand
{
    protected $group       = 'Shield';
    protected $name        = 'permissions';
    protected $description = 'Manage or audit permissions for CodeIgniter Shield';
    protected $usage       = 'permissions:list | permissions:audit';

    public function run(array $params)
    {
        $action = $params[0] ?? 'list';

        if (! in_array($action, ['list', 'audit'])) {
            CLI::write('Usage: php spark permissions:list | permissions:audit', 'yellow');
            return;
        }

        match ($action) {
            'list'  => $this->listPermissions(),
            'audit' => $this->auditPermissions(),
        };
    }

    /**
     * Displays all defined permissions from Config\AuthGroups
     */
    protected function listPermissions(): void
    {
        $config = new AuthGroups();
        $permissions = $config->permissions ?? [];

        if (empty($permissions)) {
            CLI::write('⚠️  No permissions defined in Config\\AuthGroups.php', 'yellow');
            return;
        }

        CLI::write('Defined Permissions:', 'green');
        CLI::newLine();

        foreach ($permissions as $key => $desc) {
            CLI::write(sprintf('  %-25s %s', CLI::color($key, 'yellow'), $desc));
        }

        CLI::newLine();
        CLI::write('✅  Total: ' . count($permissions) . ' permissions found.', 'green');
    }

    /**
     * Audits the auth_permissions_users table for invalid permissions.
     */
    protected function auditPermissions(): void
    {
        $db = db_connect();
        $config = new AuthGroups();
        $validPermissions = array_keys($config->permissions ?? []);

        if (empty($validPermissions)) {
            CLI::write('⚠️  No valid permissions defined in Config\\AuthGroups.php', 'yellow');
            return;
        }

        // Check if auth_permissions_users table exists
        $tables = array_map('strtolower', $db->listTables());
        if (! in_array('auth_permissions_users', $tables)) {
            CLI::write('❌  Table auth_permissions_users not found.', 'red');
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
            CLI::write('✅  No invalid user permissions found.', 'green');
            return;
        }

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
        CLI::write('Tip: You can manually remove invalid permissions using phpMyAdmin or via a cleanup task.', 'gray');
    }
}
