<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/auth/login')
                             ->with('error', lang('Auth.pleaseLoginFirst'));
        }

        // H2: Session-version check — detect sessions invalidated by force-logout / password reset.
        // Runs before the is_admin check so demoted+force-logged-out admins cannot bypass via
        // a stale session that still has is_admin=true in session storage.
        $sessionVersion = session()->get('session_version');
        if ($sessionVersion !== null) {
            $dbUser = (new UserModel())->select('session_version, is_active, is_admin')
                                      ->find(session()->get('user_id'));

            if (! $dbUser
                || (int) $dbUser['session_version'] !== (int) $sessionVersion
                || empty($dbUser['is_active'])
            ) {
                service('response')->setCookie('remember_me', '', time() - 3600, '', '/', '', false, true, 'Lax');
                session()->destroy();
                return redirect()->to('/auth/login')
                                 ->with('error', lang('Auth.sessionInvalidated'));
            }

            // H2: Re-check is_admin from DB — catches demoted admins whose session predates demotion.
            if (empty($dbUser['is_admin'])) {
                session()->set('is_admin', false);
                return redirect()->to('/dashboard')
                                 ->with('error', lang('Auth.accessDenied'));
            }
        }

        if (! session()->get('is_admin')) {
            return redirect()->to('/dashboard')
                             ->with('error', lang('Auth.accessDenied'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
