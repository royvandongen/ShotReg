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
     * M7: Atomically claim an invite — sets used_at only if it is still NULL and not expired.
     * Returns true if the claim succeeded (this process won the race), false otherwise.
     */
    public function atomicMarkUsed(int $id): bool
    {
        $this->db->query(
            'UPDATE invites SET used_at = NOW() WHERE id = ? AND used_at IS NULL AND expires_at > NOW()',
            [$id]
        );

        return $this->db->affectedRows() > 0;
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
     * F23: Revoke all pending invites from a user — hard-deletes them so the tokens
     * cannot be replayed even if the expiry-time check is bypassed.
     */
    public function revokeByUser(int $userId): int
    {
        $this->where('invited_by', $userId)
             ->where('used_at', null)
             ->where('expires_at >', date('Y-m-d H:i:s'))
             ->delete();

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
