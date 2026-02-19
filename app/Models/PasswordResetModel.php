<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table            = 'password_resets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'token',
        'email',
        'used_at',
        'expires_at',
    ];

    /**
     * Create a new password reset token valid for 60 minutes.
     * Invalidates any existing unused tokens for this email first.
     */
    public function createToken(string $email): string
    {
        // Expire any existing unused tokens for this email
        $this->where('email', $email)
             ->where('used_at', null)
             ->set('expires_at', date('Y-m-d H:i:s', strtotime('-1 second')))
             ->update();

        $token = bin2hex(random_bytes(32));

        $this->insert([
            'token'      => $token,
            'email'      => $email,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+60 minutes')),
        ]);

        return $token;
    }

    /**
     * Find a valid (unused, unexpired) token.
     */
    public function findValid(string $token): ?array
    {
        return $this->where('token', $token)
                    ->where('used_at', null)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->first();
    }

    /**
     * Mark a token as used.
     */
    public function markUsed(int $id): void
    {
        $this->update($id, ['used_at' => date('Y-m-d H:i:s')]);
    }
}
