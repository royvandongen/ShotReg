<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoToWeapons extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('weapons', [
            'photo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'notes',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('weapons', 'photo');
    }
}
