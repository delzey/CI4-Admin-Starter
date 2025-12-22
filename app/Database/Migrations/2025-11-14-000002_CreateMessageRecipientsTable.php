<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessageRecipientsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'message_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'user_id'    => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'folder'     => [
                'type'       => 'VARCHAR',
                'constraint' => 20,  // 'inbox', 'outbox', 'system'
                'null'       => false,
            ],
            'is_read'    => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'null'    => false,
                'default' => 0,
            ],
            'is_deleted' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'null'    => false,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'folder', 'is_deleted']);
        $this->forge->addKey('message_id');

        $this->forge->addForeignKey('message_id', 'messages', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('message_recipients');
    }

    public function down()
    {
        $this->forge->dropTable('message_recipients', true);
    }
}
