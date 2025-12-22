<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryIdToMenus extends Migration
{
    public function up()
    {
        $this->forge->addColumn('menus', [
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'parent_id',
            ],
        ]);

        $this->db->query('ALTER TABLE menus ADD CONSTRAINT menus_category_fk FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE SET NULL');
    }

    public function down()
    {
        $this->forge->dropForeignKey('menus', 'menus_category_fk');
        $this->forge->dropColumn('menus', 'category_id');
    }
}
