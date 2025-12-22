<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenus extends Migration
{
    public function up()
    {
        // menu_categories
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'menu_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'permission_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('menu_categories');
    }

    public function down()
    {
        $this->forge->dropTable('menu_categories');
    }
}
