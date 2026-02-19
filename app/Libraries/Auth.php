<?php

namespace App\Libraries;

use App\Models\AppSettingModel;
use App\Models\UserModel;
use App\Models\UserOptionModel;
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
            // verifyKeyNewer only accepts codes whose TOTP counter is strictly newer than
            // the last accepted counter, preventing replay of already-used codes.
            // Window=1 allows ±1 period (30 s) to account for clock drift.
            $result = $this->google2fa->verifyKeyNewer($secret, $code, $lastTimestamp, 1);
            return $result !== false ? $result : false;
        }

        // First-time use (no previous timestamp stored): allow ±1 period for clock drift.
        $result = $this->google2fa->verifyKey($secret, $code, 1);
        return $result ? (int) floor(microtime(true) / 30) : false;
    }

    public function setLoggedIn(array $user): void
    {
        session()->regenerate();
        session()->set([
            'user_id'      => $user['id'],
            'username'     => $user['username'],
            'is_admin'     => (bool) ($user['is_admin'] ?? false),
            'totp_enabled' => (bool) ($user['totp_enabled'] ?? false),
            'locale'       => $user['locale'] ?? 'en',
            'logged_in'    => true,
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

    public function logout(): void
    {
        session()->destroy();
    }
}
