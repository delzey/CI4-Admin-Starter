<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'body' => [
                'type' => 'TEXT',
                'null' => false,
            ],

            'sent_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,      // FIXED
                'default'  => null,       // FIXED
            ],

            'sent_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('sent_by');

        // FIXED FK — must use SET NULL ONLY IF column is nullable
        $this->forge->addForeignKey(
            'sent_by',
            'users',
            'id',
            'SET NULL', // on delete
            'SET NULL'  // on update
        );

        $this->forge->createTable('messages');
    }

    public function down()
    {
        $this->forge->dropTable('messages');
    }
}
