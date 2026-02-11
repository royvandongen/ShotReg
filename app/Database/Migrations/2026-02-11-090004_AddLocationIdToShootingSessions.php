<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocationIdToShootingSessions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('shooting_sessions', [
            'location_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'user_id',
            ],
        ]);

        // Add foreign key
        $this->db->query('ALTER TABLE shooting_sessions ADD CONSTRAINT fk_sessions_location FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE shooting_sessions DROP FOREIGN KEY fk_sessions_location');
        $this->forge->dropColumn('shooting_sessions', 'location_id');
    }
}
