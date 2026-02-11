<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameOwnershipOrganizationToAssociation extends Migration
{
    public function up()
    {
        // Change ENUM values and update existing data
        $this->db->query("ALTER TABLE weapons MODIFY COLUMN ownership ENUM('personal','organization','association') NOT NULL DEFAULT 'personal'");
        $this->db->query("UPDATE weapons SET ownership = 'association' WHERE ownership = 'organization'");
        $this->db->query("ALTER TABLE weapons MODIFY COLUMN ownership ENUM('personal','association') NOT NULL DEFAULT 'personal'");

        // Update user settings that stored 'organization' as default
        $this->db->query("UPDATE user_settings SET setting_value = 'association' WHERE setting_key = 'default_ownership' AND setting_value = 'organization'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE weapons MODIFY COLUMN ownership ENUM('personal','association','organization') NOT NULL DEFAULT 'personal'");
        $this->db->query("UPDATE weapons SET ownership = 'organization' WHERE ownership = 'association'");
        $this->db->query("ALTER TABLE weapons MODIFY COLUMN ownership ENUM('personal','organization') NOT NULL DEFAULT 'personal'");

        $this->db->query("UPDATE user_settings SET setting_value = 'organization' WHERE setting_key = 'default_ownership' AND setting_value = 'association'");
    }
}
