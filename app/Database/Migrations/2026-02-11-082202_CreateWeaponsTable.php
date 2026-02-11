<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWeaponsTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['pistol', 'rifle', 'shotgun', 'revolver', 'other'],
                'default'    => 'pistol',
            ],
            'caliber' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'sighting' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'ownership' => [
                'type'       => 'ENUM',
                'constraint' => ['personal', 'organization'],
                'default'    => 'personal',
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
        $this->forge->createTable('weapons');
    }

    public function down()
    {
        $this->forge->dropTable('weapons');
    }
}
