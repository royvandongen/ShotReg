<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCiSessionsTable extends Migration
{
    public function up()
    {
        // CI4 DatabaseHandler requires this exact schema (matchIP = false variant)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `ci_sessions` (
                `id`         VARCHAR(128)  NOT NULL,
                `ip_address` VARCHAR(45)   NOT NULL,
                `timestamp`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `data`       BLOB          NOT NULL,
                PRIMARY KEY (`id`),
                KEY `ci_sessions_timestamp` (`timestamp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function down()
    {
        $this->forge->dropTable('ci_sessions', true);
    }
}
