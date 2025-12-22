<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\AuthGroups;
use CodeIgniter\Database\Exceptions\DatabaseException;

class AuthPermissionsSeeder extends Seeder
{
    public function run()
    {
        $config = new AuthGroups();
        $permissions = $config->permissions ?? [];

        if (empty($permissions)) {
            echo "No permissions found in Config\\AuthGroups.\n";
            return;
        }

        $db = db_connect();

        try {
            // Check if table exists (some Shield installs skip the table)
            $tables = array_column($db->listTables(), 'Tables_in_' . $db->database);
            if (! in_array('auth_permissions', $tables)) {
                echo "⚠️  auth_permissions table not found, skipping DB sync.\n";
                return;
            }

            foreach ($permissions as $key => $desc) {
                $exists = $db->table('auth_permissions')
                    ->where('permission', $key)
                    ->countAllResults();

                if (! $exists) {
                    $db->table('auth_permissions')->insert([
                        'permission'   => $key,
                        'description'  => $desc,
                        'created_at'   => date('Y-m-d H:i:s'),
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);

                    echo "✅ Inserted permission: {$key}\n";
                } else {
                    echo "⏭️  Skipped (already exists): {$key}\n";
                }
            }

            echo "✅ Permissions sync complete.\n";

        } catch (DatabaseException $e) {
            echo "❌ Database error: {$e->getMessage()}\n";
        }
    }
}
