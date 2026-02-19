<?php

namespace App\Filters;

use App\Libraries\Auth;
use App\Models\AppSettingModel;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('logged_in')) {
            // Try remember-me auto-login before redirecting to login page
            $cookie = $request->getCookie('remember_me');
            if ($cookie) {
                $auth   = new Auth();
                $result = $auth->attemptRememberLogin($cookie);

                if ($result !== false) {
                    // Rotate the cookie value
                    service('response')->setCookie(
                        'remember_me',
                        $result['new_cookie'],
                        time() + 30 * DAY,
                        '',
                        '/',
                        '',
                        false,
                        true,
                        'Lax'
                    );

                    if ($result['user']['totp_enabled'] && ! $result['totp_trusted']) {
                        // Device not TOTP-trusted yet — require TOTP verification
                        session()->regenerate(true);
                        session()->set([
                            'pending_2fa_user_id'        => $result['user']['id'],
                            'pending_remember_selector'  => $result['selector'],
                        ]);
                        return redirect()->to('/auth/verify2fa');
                    }

                    // Full auto-login: no TOTP required for this device
                    $auth->setLoggedIn($result['user']);
                    session()->set('remember_selector', $result['selector']);
                }
            }

            if (! session()->get('logged_in')) {
                return redirect()->to('/auth/login')
                                 ->with('error', lang('Auth.pleaseLoginFirst'));
            }
        }

        // M9/F16: Session-version check runs BEFORE the exempt-paths block so that a
        // force-logged-out user cannot slip through to setup2fa on a stale session.
        $sessionVersion = session()->get('session_version');
        if ($sessionVersion !== null) {
            $dbUser = (new UserModel())->select('session_version, is_active')
                                      ->find(session()->get('user_id'));

            if (! $dbUser
                || (int) $dbUser['session_version'] !== (int) $sessionVersion
                || empty($dbUser['is_active'])
            ) {
                // Delete remember-me cookie if present (token already deleted server-side)
                service('response')->setCookie('remember_me', '', time() - 3600, '', '/', '', false, true, 'Lax');
                session()->destroy();
                return redirect()->to('/auth/login')
                                 ->with('error', lang('Auth.sessionInvalidated'));
            }
        }

        // Normalize current path
        $currentPath = strtolower(trim($request->getUri()->getPath(), '/'));

        // Exempt paths skip the 2FA enforcement check
        $exemptPaths = ['auth/setup2fa', 'auth/logout'];
        if (in_array($currentPath, $exemptPaths, true)) {
            session()->remove('redirecting_to_setup2fa');
            return null;
        }

        // 2FA enforcement
        if (! session()->get('totp_enabled')) {
            $settingModel = new AppSettingModel();
            if ($settingModel->getValue('force_2fa', '0') === '1') {
                if (! session()->get('redirecting_to_setup2fa')) {
                    session()->regenerate(true);
                    session()->set('redirecting_to_setup2fa', true);
                    return redirect()->to('/auth/setup2fa')
                                     ->with('error', lang('Auth.2faRequired'));
                }
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
