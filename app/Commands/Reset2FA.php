<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Reset2FA extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:reset-2fa';
    protected $description = 'Reset 2FA for a user by username or ID';

    public function run(array $params)
    {
        $identifier = $params[0] ?? null;

        if (! $identifier) {
            CLI::error('Usage: php spark auth:reset-2fa <username|user_id>');
            CLI::write('Example: php spark auth:reset-2fa test');
            CLI::write('Example: php spark auth:reset-2fa 1');
            return 1;
        }

        $userModel = new UserModel();

        // Try to find user by ID first (if numeric), then by username
        if (is_numeric($identifier)) {
            $user = $userModel->find((int) $identifier);
        } else {
            $user = $userModel->where('username', $identifier)->first();
        }

        if (! $user) {
            CLI::error("User not found: {$identifier}");
            return 1;
        }

        // Check if user has 2FA enabled
        if (! $user['totp_enabled']) {
            CLI::write("User '{$user['username']}' (ID: {$user['id']}) does not have 2FA enabled.", 'yellow');
            return 0;
        }

        // Reset 2FA
        $userModel->update($user['id'], [
            'totp_secret'         => null,
            'totp_enabled'        => 0,
            'totp_last_timestamp' => null,
        ]);

        CLI::write("2FA reset successfully for user '{$user['username']}' (ID: {$user['id']}).", 'green');
        CLI::write("User can now set up 2FA again from the settings page.");
        return 0;
    }
}
