<?php

namespace App\Models;

use CodeIgniter\Model;

class UserTokenModel extends Model
{
    protected $table            = 'user_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'user_id',
        'selector',
        'hashed_validator',
        'totp_trusted',
        'device_name',
        'ip_address',
        'last_used_at',
        'expires_at',
        'created_at',
    ];

    /**
     * Create a new remember-me token. Returns the cookie value (selector:validator)
     * and the selector separately.
     */
    public function createToken(
        int $userId,
        bool $totpTrusted = false,
        ?string $deviceName = null,
        ?string $ipAddress = null
    ): array {
        $selector  = bin2hex(random_bytes(12)); // 24-char hex
        $validator = bin2hex(random_bytes(32)); // 64-char hex
        $now       = date('Y-m-d H:i:s');

        $this->insert([
            'user_id'          => $userId,
            'selector'         => $selector,
            'hashed_validator' => hash('sha256', $validator),
            'totp_trusted'     => $totpTrusted ? 1 : 0,
            'device_name'      => $deviceName,
            'ip_address'       => $ipAddress,
            'last_used_at'     => $now,
            'expires_at'       => date('Y-m-d H:i:s', strtotime('+30 days')),
            'created_at'       => $now,
        ]);

        return [
            'cookie_value' => $selector . ':' . $validator,
            'selector'     => $selector,
        ];
    }

    /**
     * Validate a remember-me cookie value and rotate the validator.
     * On success returns the token row + 'new_cookie_value'.
     * On failure (invalid/expired/stolen) returns false.
     */
    public function findAndValidate(string $cookieValue): array|false
    {
        $parts = explode(':', $cookieValue, 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return false;
        }

        [$selector, $validator] = $parts;

        $token = $this->where('selector', $selector)
                      ->where('expires_at >', date('Y-m-d H:i:s'))
                      ->first();

        if (! $token) {
            return false;
        }

        if (! hash_equals($token['hashed_validator'], hash('sha256', $validator))) {
            // Potential token theft: delete the compromised token
            $this->delete($token['id']);
            return false;
        }

        // Rotate: issue a new validator, keep the same selector
        $newValidator = bin2hex(random_bytes(32));
        $this->update($token['id'], [
            'hashed_validator' => hash('sha256', $newValidator),
            'last_used_at'     => date('Y-m-d H:i:s'),
        ]);

        $token['new_cookie_value'] = $selector . ':' . $newValidator;
        return $token;
    }

    /**
     * Mark a token as TOTP-trusted so the device can skip TOTP on auto-login.
     */
    public function markTotpTrusted(string $selector): void
    {
        $this->where('selector', $selector)->set(['totp_trusted' => 1])->update();
    }

    /**
     * Revoke a single token by its selector.
     */
    public function revokeBySelector(string $selector): void
    {
        $this->where('selector', $selector)->delete();
    }

    /**
     * Revoke all tokens for a user.
     */
    public function revokeAllForUser(int $userId): void
    {
        $this->where('user_id', $userId)->delete();
    }

    /**
     * Revoke all tokens for a user except one selector (e.g. current device).
     */
    public function revokeAllForUserExcept(int $userId, string $exceptSelector): void
    {
        $this->where('user_id', $userId)
             ->where('selector !=', $exceptSelector)
             ->delete();
    }

    /**
     * Return all non-expired tokens for a user, newest first.
     */
    public function getActiveForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->orderBy('last_used_at', 'DESC')
                    ->findAll();
    }

    /**
     * Parse a human-readable device name from a User-Agent string.
     */
    public static function parseDeviceName(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'Unknown device';
        }

        if (preg_match('/iPad/i', $userAgent)) {
            return 'iPad';
        }
        if (preg_match('/iPhone|iPod/i', $userAgent)) {
            return 'iPhone';
        }
        if (preg_match('/Android/i', $userAgent)) {
            $mobile = preg_match('/Mobile/i', $userAgent);
            return $mobile ? 'Android phone' : 'Android tablet';
        }
        if (preg_match('/Windows/i', $userAgent)) {
            return 'Windows';
        }
        if (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
            return 'Mac';
        }
        if (preg_match('/Linux/i', $userAgent)) {
            return 'Linux';
        }

        return 'Unknown device';
    }
}
