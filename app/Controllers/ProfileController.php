<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\UserModel;
use App\Models\UserTokenModel;

class ProfileController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $user = $this->userModel->find(session()->get('user_id'));

        return view('profile/index', [
            'user' => $user,
        ]);
    }

    public function update()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        $rules = [
            'username' => "required|min_length[3]|max_length[50]|is_unique[users.username,id,{$userId}]",
            'email'    => "required|valid_email|is_unique[users.email,id,{$userId}]",
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return view('profile/index', [
                'user'   => $user,
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $data = [
            'username'       => $this->request->getPost('username'),
            'email'          => $this->request->getPost('email'),
            'first_name'     => $this->request->getPost('first_name') ?: null,
            'last_name'      => $this->request->getPost('last_name') ?: null,
            'knsa_member_id' => $this->request->getPost('knsa_member_id') ?: null,
        ];

        $this->userModel->update($userId, $data);

        // Update session username if changed
        session()->set('username', $data['username']);

        return redirect()->to('/profile')
                         ->with('success', lang('Profile.updated'));
    }

    public function changePassword()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return view('profile/index', [
                'user'   => $user,
                'errors' => $this->validator->getErrors(),
            ]);
        }

        if (! password_verify($this->request->getPost('current_password'), $user['password_hash'])) {
            return view('profile/index', [
                'user'   => $user,
                'errors' => ['current_password' => lang('Profile.wrongPassword')],
            ]);
        }

        $this->userModel->update($userId, [
            'password_hash' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT),
        ]);

        // Revoke all sessions (including remember-me tokens) for security
        (new Auth())->revokeAllSessions($userId);

        // Delete the remember-me cookie — user must log in again
        service('response')->setCookie('remember_me', '', time() - 3600, '', '/', '', false, true, 'Lax');

        // Destroy the current session so the user is prompted to re-authenticate
        session()->destroy();

        return redirect()->to('/auth/login')
                         ->with('success', lang('Profile.passwordChangedSignedOut'));
    }

    /**
     * Show all active remembered sessions (user_tokens) for the logged-in user.
     */
    public function sessions()
    {
        $userId     = (int) session()->get('user_id');
        $tokenModel = new UserTokenModel();
        $tokens     = $tokenModel->getActiveForUser($userId);

        return view('profile/sessions', [
            'tokens'          => $tokens,
            'currentSelector' => session()->get('remember_selector'),
        ]);
    }

    /**
     * Sign out a single remembered session by selector.
     */
    public function revokeSession(string $selector)
    {
        $userId     = (int) session()->get('user_id');
        $tokenModel = new UserTokenModel();

        // Verify the token belongs to this user
        $token = $tokenModel->where('selector', $selector)
                            ->where('user_id', $userId)
                            ->first();

        if ($token) {
            $tokenModel->revokeBySelector($selector);

            // If revoking the current remember-me session, clear the cookie too
            if (session()->get('remember_selector') === $selector) {
                service('response')->setCookie('remember_me', '', time() - 3600, '', '/', '', false, true, 'Lax');
                session()->remove('remember_selector');
            }
        }

        return redirect()->to('/profile/sessions')
                         ->with('success', lang('Profile.sessionRevoked'));
    }

    /**
     * Sign out all other remembered sessions (keep only the current one).
     */
    public function revokeOtherSessions()
    {
        $userId          = (int) session()->get('user_id');
        $currentSelector = session()->get('remember_selector') ?? '';
        $tokenModel      = new UserTokenModel();

        if ($currentSelector) {
            $tokenModel->revokeAllForUserExcept($userId, $currentSelector);
        } else {
            $tokenModel->revokeAllForUser($userId);
        }

        return redirect()->to('/profile/sessions')
                         ->with('success', lang('Profile.otherSessionsRevoked'));
    }
}
