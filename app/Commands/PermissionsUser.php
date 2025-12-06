<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;

/**
 * Displays a user's current permissions (direct + inherited).
 *
 * Usage:
 *   php spark permissions:user <user_id>
 */
class PermissionsUser extends BaseCommand
{
    protected $group       = 'Shield';
    protected $name        = 'permissions:user';
    protected $description = 'Displays a user’s effective permissions.';
    protected $usage       = 'permissions:user <user_id>';
    protected $arguments   = [
        'user_id' => 'The ID of the user to inspect.',
    ];

    public function run(array $params)
    {
        $userId = $params[0] ?? null;
        if (! $userId) {
            CLI::write('Usage: php spark permissions:user <user_id>', 'yellow');
            return;
        }

        $userId = (int) $userId;
        $userModel = model(UserModel::class);
        $user = $userModel->find($userId);

        if (! $user instanceof User) {
            CLI::write("❌  User ID {$userId} not found.", 'red');
            return;
        }

        CLI::write("User #{$userId}: " . CLI::color($user->username ?? $user->email, 'yellow'));
        CLI::write(str_repeat('-', 60));

        // --- Show direct permissions ------------------------------------------
        $direct = $user->getPermissions();
        if (empty($direct)) {
            CLI::write('No direct permissions assigned.', 'gray');
        } else {
            CLI::write('Direct Permissions:', 'cyan');
            foreach ($direct as $perm) {
                CLI::write('  • ' . CLI::color($perm, 'green'));
            }
        }

        // --- Show group membership --------------------------------------------
        $groups = $user->getGroups();
        if (! empty($groups)) {
            CLI::newLine();
            CLI::write('Groups:', 'cyan');
            foreach ($groups as $group) {
                CLI::write('  • ' . CLI::color($group, 'yellow'));
            }
        }

        CLI::newLine();
        CLI::write('✅  Permission check complete.', 'green');
    }
}
