<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsApprovedToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'is_approved' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_admin',
            ],
        ]);

        // Auto-approve all existing users
        $this->db->table('users')->update(['is_approved' => 1]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'is_approved');
    }
}
