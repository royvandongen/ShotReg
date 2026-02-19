<?php

namespace App\Models;

use CodeIgniter\Model;

class InviteModel extends Model
{
    protected $table            = 'invites';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'token',
        'email',
        'invited_by',
        'used_at',
        'expires_at',
    ];

    /**
     * Create a new invite token valid for 48 hours.
     */
    public function createInvite(string $email, ?int $invitedBy): array
    {
        $token = bin2hex(random_bytes(32));

        $id = $this->insert([
            'token'      => $token,
            'email'      => $email,
            'invited_by' => $invitedBy,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+48 hours')),
        ]);

        return ['id' => $id, 'token' => $token];
    }

    /**
     * Find a valid (unused, unexpired) invite by token.
     */
    public function findValid(string $token): ?array
    {
        return $this->where('token', $token)
                    ->where('used_at', null)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->first();
    }

    /**
     * Mark an invite as used.
     */
    public function markUsed(int $id): void
    {
        $this->update($id, ['used_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Count invites sent by a user (all time, including used/expired).
     */
    public function countByUser(int $userId): int
    {
        return $this->where('invited_by', $userId)->countAllResults();
    }

    /**
     * Count pending (unused, unexpired) invites by a user.
     */
    public function countPendingByUser(int $userId): int
    {
        return $this->where('invited_by', $userId)
                    ->where('used_at', null)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->countAllResults();
    }

    /**
     * Get remaining invites for a user, considering per-user override and global limit.
     * Returns null if unlimited.
     */
    public function getRemainingForUser(int $userId): ?int
    {
        $userSettingModel = new UserSettingModel();
        $appSettingModel  = new AppSettingModel();

        // Check per-user override (-1 = use global, 0 = unlimited, N = exact limit)
        $override = $userSettingModel->getValue($userId, 'invite_limit_override');

        if ($override !== null && (int) $override === 0) {
            return null; // unlimited for this user
        }

        $limit = ($override !== null && (int) $override > 0)
            ? (int) $override
            : (int) $appSettingModel->getValue('user_invite_limit', '5');

        if ($limit === 0) {
            return null; // global unlimited
        }

        $used = $this->countByUser($userId);

        return max(0, $limit - $used);
    }

    /**
     * Revoke all pending invites from a user by marking them expired now.
     */
    public function revokeByUser(int $userId): int
    {
        $past = date('Y-m-d H:i:s', strtotime('-1 second'));

        $this->where('invited_by', $userId)
             ->where('used_at', null)
             ->where('expires_at >', date('Y-m-d H:i:s'))
             ->set('expires_at', $past)
             ->update();

        return $this->db->affectedRows();
    }

    /**
     * Get all invites with sender info joined.
     */
    public function getAllWithSenders(): array
    {
        return $this->select('invites.*, users.username AS invited_by_username')
                    ->join('users', 'users.id = invites.invited_by', 'left')
                    ->orderBy('invites.created_at', 'DESC')
                    ->findAll();
    }
}
