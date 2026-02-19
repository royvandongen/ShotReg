<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * F21: Expand hashed_validator from VARCHAR(64) to VARCHAR(255) so the column
 * can accommodate future hash algorithm changes (e.g. SHA-512 produces 128 hex chars,
 * or a prefixed format like "$sha256$<hex>").
 */
class ExpandHashedValidatorColumn extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('user_tokens', [
            'hashed_validator' => [
                'name'       => 'hashed_validator',
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('user_tokens', [
            'hashed_validator' => [
                'name'       => 'hashed_validator',
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
            ],
        ]);
    }
}
