<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Libraries\Mailer;
use App\Models\AppSettingModel;
use App\Models\PasswordResetModel;
use App\Models\UserModel;

class AuthController extends BaseController
{
    protected Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    protected function sanitizeIpForCache(string $ip): string
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '_', $ip);
    }

    public function login()
    {
        if ($this->request->getMethod() === 'POST') {
            // Rate limit: 5 login attempts per minute per IP
            $throttler = service('throttler');
            $throttleKey = 'login_' . $this->sanitizeIpForCache($this->request->getIPAddress());
            if (! $throttler->check($throttleKey, 5, MINUTE)) {
                return view('auth/login', [
                    'errors' => ['login' => lang('Auth.tooManyAttempts')],
                ]);
            }

            $rules = [
                'username' => 'required',
                'password' => 'required',
            ];

            if (! $this->validateData($this->request->getPost(), $rules)) {
                return view('auth/login', ['errors' => $this->validator->getErrors()]);
            }

            $user = $this->auth->attemptLogin(
                $this->request->getPost('username'),
                $this->request->getPost('password')
            );

            if ($user === 'pending') {
                return view('auth/login', ['errors' => ['login' => lang('Auth.accountPending')]]);
            }

            if ($user === 'disabled') {
                return view('auth/login', ['errors' => ['login' => lang('Auth.accountDisabled')]]);
            }

            if (! $user) {
                log_message('warning', 'Failed login attempt for user: {username} from IP: {ip}', [
                    'username' => $this->request->getPost('username'),
                    'ip'       => $this->request->getIPAddress(),
                ]);
                return view('auth/login', ['errors' => ['login' => lang('Auth.invalidCredentials')]]);
            }

            if ($user['totp_enabled']) {
                session()->set('pending_2fa_user_id', $user['id']);
                return redirect()->to('/auth/verify2fa');
            }

            $this->auth->setLoggedIn($user);
            // Check if 2FA is forced globally
            $settingModel = new AppSettingModel();
            if ($settingModel->getValue('force_2fa', '0') === '1') {
                session()->set('redirecting_to_setup2fa', true);
                return redirect()->to('/auth/setup2fa')
                                 ->with('error', lang('Auth.2faRequired'));
            }
            
            return redirect()->to('/dashboard');

        }

        return view('auth/login');
    }

    public function verify2fa()
    {
        $userId = session()->get('pending_2fa_user_id');
        if (! $userId) {
            return redirect()->to('/auth/login');
        }

        if ($this->request->getMethod() === 'POST') {
            // Rate limit: 5 TOTP attempts per minute per IP
            $throttler = service('throttler');
            $throttleKey = '2fa_' . $this->sanitizeIpForCache($this->request->getIPAddress());
            if (! $throttler->check($throttleKey, 5, MINUTE)) {
                return view('auth/verify2fa', [
                    'error' => lang('Auth.tooMany2faAttempts'),
                ]);
            }

            $code = $this->request->getPost('totp_code');
            $userModel = new UserModel();
            $user = $userModel->find($userId);

            $timestamp = $this->auth->verifyTotp(
                $user['totp_secret'],
                $code,
                $user['totp_last_timestamp']
            );

            if ($timestamp !== false) {
                $userModel->update($userId, ['totp_last_timestamp' => $timestamp]);
                session()->remove('pending_2fa_user_id');
                $this->auth->setLoggedIn($user);
                return redirect()->to('/dashboard');
            }

            return view('auth/verify2fa', ['error' => lang('Auth.invalidOrExpiredCode')]);
        }

        return view('auth/verify2fa');
    }

    public function register()
    {
        if (! Auth::isRegistrationEnabled()) {
            return redirect()->to('/auth/login')
                             ->with('error', lang('Auth.registrationDisabled'));
        }

        if ($this->request->getMethod() === 'POST') {
            // Rate limit: 3 registrations per hour per IP
            $throttler = service('throttler');
            $throttleKey = 'register_' . $this->sanitizeIpForCache($this->request->getIPAddress());
            if (! $throttler->check($throttleKey, 3, HOUR)) {
                return view('auth/register', [
                    'errors' => ['register' => lang('Auth.tooManyRegistrations')],
                ]);
            }

            $rules = [
                'username'         => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
                'email'            => 'required|valid_email|is_unique[users.email]',
                'password'         => 'required|min_length[8]',
                'password_confirm' => 'required|matches[password]',
            ];

            if (! $this->validateData($this->request->getPost(), $rules)) {
                return view('auth/register', ['errors' => $this->validator->getErrors()]);
            }

            $this->auth->register([
                'username' => $this->request->getPost('username'),
                'email'    => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
            ]);

            return redirect()->to('/auth/login')
                             ->with('success', lang('Auth.accountCreatedPendingApproval'));
        }

        return view('auth/register');
    }

    public function setup2fa()
    {
        $userId = session()->get('user_id');
        if (! $userId) {
            return redirect()->to('/auth/login')
                             ->with('error', lang('Auth.pleaseLoginFirst'));
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        
        if (! $user) {
            return redirect()->to('/auth/login')
                             ->with('error', lang('Auth.userNotFound'));
        }

        if ($this->request->getMethod() === 'POST') {
            $secret = session()->get('pending_totp_secret');
            $code   = trim($this->request->getPost('totp_code') ?? '');

            // Validate code format
            if (empty($code) || ! preg_match('/^\d{6}$/', $code)) {
                $secret = session()->get('pending_totp_secret');
                if (empty($secret)) {
                    $secret = $this->auth->generateTotpSecret();
                    session()->set('pending_totp_secret', $secret);
                }
                $qrSvg = $this->auth->getTotpQrCodeSvg($user['email'], $secret);
                return view('auth/setup2fa', [
                    'qrSvg'  => $qrSvg,
                    'secret' => $secret,
                    'error'  => lang('Auth.enterValid6Digit'),
                ]);
            }

            if (empty($secret)) {
                // Secret expired or missing, generate a new one
                $secret = $this->auth->generateTotpSecret();
                session()->set('pending_totp_secret', $secret);
                $qrSvg = $this->auth->getTotpQrCodeSvg($user['email'], $secret);
                return view('auth/setup2fa', [
                    'qrSvg'  => $qrSvg,
                    'secret' => $secret,
                    'error'  => lang('Auth.sessionExpiredRescan'),
                ]);
            }

            $valid = $this->auth->verifyTotp($secret, $code);

            if ($valid !== false) {
                $userModel->update($user['id'], [
                    'totp_secret'  => $secret,
                    'totp_enabled' => 1,
                ]);
                session()->remove('pending_totp_secret');
                session()->set('totp_enabled', true);
                return redirect()->to('/dashboard')
                                 ->with('success', lang('Auth.2faEnabled'));
            }

            // Invalid code - keep same secret and QR code
            $qrSvg = $this->auth->getTotpQrCodeSvg($user['email'], $secret);
            return view('auth/setup2fa', [
                'qrSvg'  => $qrSvg,
                'secret' => $secret,
                'error'  => lang('Auth.invalidCode'),
            ]);
        }

        // GET request - generate new secret if needed
        // Note: Flash data is automatically removed after being displayed once
        $secret = session()->get('pending_totp_secret');
        if (empty($secret)) {
            $secret = $this->auth->generateTotpSecret();
            session()->set('pending_totp_secret', $secret);
        }

        $qrSvg = $this->auth->getTotpQrCodeSvg($user['email'], $secret);

        return view('auth/setup2fa', [
            'qrSvg'  => $qrSvg,
            'secret' => $secret,
        ]);
    }

    public function forgotPassword()
    {
        if ($this->request->getMethod() === 'POST') {
            // Rate limit: 3 attempts per 10 minutes per IP
            $throttler   = service('throttler');
            $throttleKey = 'forgot_' . $this->sanitizeIpForCache($this->request->getIPAddress());
            if (! $throttler->check($throttleKey, 3, 10 * MINUTE)) {
                return view('auth/forgot_password', [
                    'errors' => ['email' => lang('Auth.tooManyAttempts')],
                ]);
            }

            $email = trim($this->request->getPost('email') ?? '');

            if (! $this->validateData(['email' => $email], ['email' => 'required|valid_email'])) {
                return view('auth/forgot_password', ['errors' => $this->validator->getErrors()]);
            }

            // Always show the same message to prevent user enumeration
            $userModel = new UserModel();
            $user      = $userModel->where('email', $email)->first();

            if ($user && ! empty($user['is_active'])) {
                $settingModel  = new AppSettingModel();
                $expiryMinutes = max(1, (int) $settingModel->getValue('password_reset_expiry_minutes', '60'));

                $resetModel = new PasswordResetModel();
                $token      = $resetModel->createToken($email, $expiryMinutes);

                $mailer = new Mailer();
                if ($mailer->isConfigured()) {
                    $mailer->sendPasswordReset($email, $token, $expiryMinutes);
                }
            }

            return redirect()->to('/auth/forgot-password')
                             ->with('success', lang('Auth.resetEmailSent'));
        }

        return view('auth/forgot_password');
    }

    public function resetPassword(string $token)
    {
        $resetModel = new PasswordResetModel();
        $reset      = $resetModel->findValid($token);

        if (! $reset) {
            return view('auth/reset_password', ['invalidToken' => true]);
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'password'         => 'required|min_length[8]',
                'password_confirm' => 'required|matches[password]',
            ];

            if (! $this->validateData($this->request->getPost(), $rules)) {
                return view('auth/reset_password', [
                    'token'  => $token,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $userModel = new UserModel();
            $userModel->where('email', $reset['email'])
                      ->set(['password_hash' => password_hash(
                          $this->request->getPost('password'),
                          PASSWORD_DEFAULT
                      )])
                      ->update();

            $resetModel->markUsed($reset['id']);

            return redirect()->to('/auth/login')
                             ->with('success', lang('Auth.passwordResetSuccess'));
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    public function logout()
    {
        $this->auth->logout();
        return redirect()->to('/auth/login');
    }
}
