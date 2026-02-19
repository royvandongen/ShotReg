<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSessionVersionToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'session_version' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 1,
                'after'      => 'is_active',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'session_version');
    }
}
