<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocaleToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'locale' => [
                'type'       => 'VARCHAR',
                'constraint' => 5,
                'default'    => 'en',
                'after'      => 'knsa_member_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'locale');
    }
}
