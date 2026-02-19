<?php

namespace App\Libraries;

use App\Models\AppSettingModel;
use App\Models\UserModel;
use App\Models\UserOptionModel;
use App\Models\UserTokenModel;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Auth
{
    protected UserModel $userModel;
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->google2fa = new Google2FA();
    }

    public function register(array $data): int|false
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        // First user (or no admin exists) becomes admin + auto-approved
        $adminExists = $this->userModel->where('is_admin', 1)->countAllResults() > 0;
        if (! $adminExists) {
            $data['is_admin']     = 1;
            $data['is_approved']  = 1;
        }

        // Save locale from current session
        if (! isset($data['locale'])) {
            $data['locale'] = session()->get('locale') ?? 'en';
        }

        $userId = $this->userModel->insert($data);

        if ($userId) {
            (new UserOptionModel())->seedDefaults($userId);
        }

        return $userId;
    }

    /**
     * @return array|string|false  User array on success, 'pending'/'disabled' for blocked accounts, false on bad credentials.
     */
    public function attemptLogin(string $usernameOrEmail, string $password): array|string|false
    {
        $user = $this->userModel
            ->groupStart()
                ->where('username', $usernameOrEmail)
                ->orWhere('email', $usernameOrEmail)
            ->groupEnd()
            ->first();

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            return false;
        }

        if (empty($user['is_approved'])) {
            return 'pending';
        }

        if (empty($user['is_active'])) {
            return 'disabled';
        }

        return $user;
    }

    public function generateTotpSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getTotpQrCodeSvg(string $email, string $secret): string
    {
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            'Shotr',
            $email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }

    public function verifyTotp(string $secret, string $code, ?int $lastTimestamp = null): int|false
    {
        if ($lastTimestamp !== null) {
            // verifyKeyNewer rejects codes whose counter is NOT strictly greater than the
            // reference value. Passing ($lastTimestamp - 1) lets the current period's code
            // be accepted on the very first login after a logout within the same 30-second
            // window, while still rejecting the previous period's code (true replay attack).
            $result = $this->google2fa->verifyKeyNewer($secret, $code, $lastTimestamp - 1, 1);
            return $result !== false ? $result : false;
        }

        // First-time use (no previous timestamp stored): use verifyKeyNewer with -1 so
        // the returned value is the actual TOTP counter (not a boolean), which is needed
        // for accurate replay prevention on subsequent logins.
        $result = $this->google2fa->verifyKeyNewer($secret, $code, -1, 1);
        return $result !== false ? $result : false;
    }

    public function setLoggedIn(array $user): void
    {
        session()->regenerate();
        session()->set([
            'user_id'         => $user['id'],
            'username'        => $user['username'],
            'is_admin'        => (bool) ($user['is_admin'] ?? false),
            'totp_enabled'    => (bool) ($user['totp_enabled'] ?? false),
            'locale'          => $user['locale'] ?? 'en',
            'logged_in'       => true,
            'session_version' => (int) ($user['session_version'] ?? 1),
        ]);
    }

    public function isLoggedIn(): bool
    {
        return (bool) session()->get('logged_in');
    }

    public function isAdmin(): bool
    {
        return (bool) session()->get('is_admin');
    }

    public static function isRegistrationEnabled(): bool
    {
        $model = new AppSettingModel();

        return $model->getValue('registration_enabled', '1') !== '0';
    }

    public function userId(): ?int
    {
        return session()->get('user_id');
    }

    /**
     * Log out: destroy the session and optionally revoke the remember-me token.
     */
    public function logout(?string $rememberCookie = null): void
    {
        if ($rememberCookie) {
            $parts = explode(':', $rememberCookie, 2);
            if (! empty($parts[0])) {
                (new UserTokenModel())->revokeBySelector($parts[0]);
            }
        }

        session()->destroy();
    }

    /**
     * Revoke all active sessions for a user:
     *  - Increments session_version (invalidates all PHP sessions on next request)
     *  - Deletes all remember-me tokens
     */
    public function revokeAllSessions(int $userId): void
    {
        $this->userModel->db->query(
            'UPDATE users SET session_version = session_version + 1 WHERE id = ?',
            [$userId]
        );

        (new UserTokenModel())->revokeAllForUser($userId);
    }

    /**
     * Attempt to log in from a remember-me cookie value.
     *
     * Returns an array with:
     *   'user'         => $userRow
     *   'totp_trusted' => bool  (whether this device can skip TOTP)
     *   'selector'     => string
     *   'new_cookie'   => string  (rotated cookie value to set in response)
     *
     * Returns false on failure.
     */
    public function attemptRememberLogin(string $cookieValue): array|false
    {
        $tokenModel = new UserTokenModel();
        $token      = $tokenModel->findAndValidate($cookieValue);

        if (! $token) {
            return false;
        }

        $user = $this->userModel->find($token['user_id']);

        if (! $user || empty($user['is_approved']) || empty($user['is_active'])) {
            // Revoke token for disabled/deleted users
            $selector = explode(':', $cookieValue)[0];
            $tokenModel->revokeBySelector($selector);
            return false;
        }

        return [
            'user'         => $user,
            'totp_trusted' => (bool) $token['totp_trusted'],
            'selector'     => explode(':', $cookieValue)[0],
            'new_cookie'   => $token['new_cookie_value'],
        ];
    }
}
