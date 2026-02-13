<?php

namespace App\Commands;

use App\Models\UserModel;
use App\Models\UserOptionModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AdminCreate extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'admin:create';
    protected $description = 'Create an admin user from environment variables (ADMIN_USERNAME, ADMIN_EMAIL, ADMIN_PASSWORD)';

    public function run(array $params)
    {
        $username = env('ADMIN_USERNAME') ?: ($params[0] ?? null);
        $email    = env('ADMIN_EMAIL')    ?: ($params[1] ?? null);
        $password = env('ADMIN_PASSWORD') ?: ($params[2] ?? null);

        if (! $username || ! $email || ! $password) {
            CLI::error('Required: ADMIN_USERNAME, ADMIN_EMAIL, ADMIN_PASSWORD environment variables (or pass as arguments).');
            return 1;
        }

        if (strlen($password) < 8) {
            CLI::error('Password must be at least 8 characters.');
            return 1;
        }

        $userModel = new UserModel();

        // Check if user already exists by username or email
        $existing = $userModel->where('username', $username)
                              ->orWhere('email', $email)
                              ->first();

        if ($existing) {
            // Promote existing user to admin
            if ($existing['is_admin']) {
                // Ensure admin is approved (may have been missed before this feature existed)
                if (empty($existing['is_approved'])) {
                    $userModel->update($existing['id'], ['is_approved' => 1]);
                    CLI::write("User '{$existing['username']}' is already an admin, now approved.", 'green');
                } else {
                    CLI::write("User '{$existing['username']}' is already an admin.", 'yellow');
                }
                return 0;
            }

            $userModel->update($existing['id'], ['is_admin' => 1, 'is_approved' => 1]);
            CLI::write("Existing user '{$existing['username']}' promoted to admin.", 'green');
            return 0;
        }

        // Create new admin user
        $userId = $userModel->insert([
            'username'      => $username,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'is_admin'      => 1,
            'is_approved'   => 1,
        ]);

        // Seed default options (lane types, sightings)
        $optionModel = new UserOptionModel();
        $optionModel->seedDefaults($userId);

        CLI::write("Admin user '{$username}' created successfully.", 'green');
        return 0;
    }
}
