<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDbBackupsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'auto_increment' => true],
            'file_path'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'label'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'user_id'    => ['type' => 'INT', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('db_backups');
    }

    public function down()
    {
        $this->forge->dropTable('db_backups');
    }
}
