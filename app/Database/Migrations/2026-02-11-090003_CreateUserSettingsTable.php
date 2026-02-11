<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'setting_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'setting_value' => [
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
        $this->forge->addUniqueKey(['user_id', 'setting_key']);
        $this->forge->createTable('user_settings');
    }

    public function down()
    {
        $this->forge->dropTable('user_settings');
    }
}
