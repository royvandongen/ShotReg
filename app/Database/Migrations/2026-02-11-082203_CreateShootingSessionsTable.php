<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShootingSessionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'weapon_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'session_date' => [
                'type' => 'DATE',
            ],
            'distance' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('weapon_id', 'weapons', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('session_date');
        $this->forge->createTable('shooting_sessions');
    }

    public function down()
    {
        $this->forge->dropTable('shooting_sessions');
    }
}
