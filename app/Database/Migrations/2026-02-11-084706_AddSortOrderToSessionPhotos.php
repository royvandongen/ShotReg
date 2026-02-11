<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSortOrderToSessionPhotos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('session_photos', [
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'file_size',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('session_photos', 'sort_order');
    }
}
