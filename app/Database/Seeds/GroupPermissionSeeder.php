<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GroupPermissionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['group_name' => 'superadmin', 'permissions' => json_encode(['*'])],
            ['group_name' => 'admin', 'permissions' => json_encode(['users.view', 'settings.view'])],
        ];

        $this->db->table('group_permissions')->insertBatch($data);
    }
}
