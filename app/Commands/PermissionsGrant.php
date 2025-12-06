<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;
use Config\AuthGroups;

/**
 * Grant or revoke a Shield permission directly from CLI.
 *
 * Usage:
 *   php spark permissions:grant 2 users.create
 *   php spark permissions:grant 2 users.create --revoke
 */
class PermissionsGrant extends BaseCommand
{
    protected $group       = 'Shield';
    protected $name        = 'permissions:grant';
    protected $description = 'Grants or revokes a permission for a user.';
    protected $usage       = 'permissions:grant <user_id> <permission> [--revoke]';
    protected $arguments   = [
        'user_id'     => 'User ID to grant or revoke the permission for.',
        'permission'  => 'Permission key (must exist in Config\\AuthGroups).',
    ];
    protected $options     = [
        '--revoke' => 'Revoke the permission instead of granting it.',
    ];

    public function run(array $params)
    {
        // --- Validate arguments -------------------------------------------------
        $userId = $params[0] ?? null;
        $permission = $params[1] ?? null;

        if (! $userId || ! $permission) {
            CLI::write('Usage: php spark permissions:grant <user_id> <permission> [--revoke]', 'yellow');
            return;
        }

        $userId = (int) $userId;
        $revoke = CLI::getOption('revoke');

        $config = new AuthGroups();
        $validPermissions = array_keys($config->permissions ?? []);

        if (! in_array($permission, $validPermissions, true)) {
            CLI::write("❌  Invalid permission key: {$permission}", 'red');
            CLI::write('Use "php spark permissions:list" to see available permissions.', 'yellow');
            return;
        }

        // --- Load user ----------------------------------------------------------
        $userModel = model(UserModel::class);
        $user = $userModel->find($userId);

        if (! $user instanceof User) {
            CLI::write("❌  User ID {$userId} not found.", 'red');
            return;
        }

        // --- Perform grant or revoke -------------------------------------------
        if ($revoke) {
            $user->removePermission($permission);
            $result = ! $user->can($permission);
            $msg = $result
                ? "🚫  Revoked '{$permission}' from user #{$userId}."
                : "⚠️  Failed to revoke '{$permission}' (still active).";
            CLI::write($msg, $result ? 'green' : 'yellow');
        } else {
            $user->addPermission($permission);
            $result = $user->can($permission);
            $msg = $result
                ? "✅  Granted '{$permission}' to user #{$userId}."
                : "⚠️  Failed to grant '{$permission}' (check DB).";
            CLI::write($msg, $result ? 'green' : 'yellow');
        }

        // --- Optional audit summary --------------------------------------------
        CLI::newLine();
        CLI::write('Current Permissions:', 'cyan');
        foreach ($user->getPermissions() as $perm) {
            CLI::write('  • ' . CLI::color($perm, 'yellow'));
        }
    }
}
