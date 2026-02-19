<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Libraries\Mailer;
use App\Models\AppSettingModel;
use App\Models\InviteModel;
use App\Models\UserModel;
use App\Models\UserOptionModel;

class InviteController extends BaseController
{
    protected InviteModel $inviteModel;
    protected AppSettingModel $settingModel;

    public function __construct()
    {
        $this->inviteModel  = new InviteModel();
        $this->settingModel = new AppSettingModel();
    }

    /**
     * Send an invite (POST). Available to admins always; to users when enabled + within limit.
     */
    public function send()
    {
        $isAdmin = (bool) session()->get('is_admin');
        $userId  = (int) session()->get('user_id');

        // Permission check
        if (! $isAdmin) {
            if ($this->settingModel->getValue('invites_enabled', '0') !== '1') {
                return redirect()->back()->with('error', lang('Invite.invitesDisabled'));
            }
            if ($this->settingModel->getValue('user_invites_enabled', '0') !== '1') {
                return redirect()->back()->with('error', lang('Invite.userInvitesDisabled'));
            }
            $remaining = $this->inviteModel->getRemainingForUser($userId);
            if ($remaining !== null && $remaining <= 0) {
                return redirect()->back()->with('error', lang('Invite.inviteLimitReached'));
            }
        }

        $email = trim($this->request->getPost('invite_email') ?? '');

        if (! $this->validateData(['email' => $email], ['email' => 'required|valid_email'])) {
            return redirect()->back()->with('error', lang('Invite.invalidEmail'));
        }

        // Don't invite existing users — admins see a specific error; regular users get
        // a generic response to prevent account enumeration via the invite form.
        $userModel = new UserModel();
        if ($userModel->where('email', $email)->first()) {
            if ($isAdmin) {
                return redirect()->back()->with('error', lang('Invite.emailAlreadyRegistered'));
            }
            return redirect()->back()->with('success', lang('Invite.inviteCreated'));
        }

        $invite = $this->inviteModel->createInvite($email, $userId);
        $baseUrl = rtrim(config('App')->baseURL, '/');
        $inviteLink = $baseUrl . '/invite/' . $invite['token'];

        // Optionally send email
        $mailer = new Mailer();
        $emailSent = false;
        if ($mailer->isConfigured()) {
            $username = session()->get('username') ?? 'Someone';
            $emailSent = $mailer->sendInvite($email, $username, $invite['token']);
        }

        $msg = $emailSent
            ? lang('Invite.inviteSentWithEmail', [$email])
            : lang('Invite.inviteCreated');

        return redirect()->back()
                         ->with('success', $msg)
                         ->with('invite_link', $inviteLink);
    }

    /**
     * Accept an invite (GET: show form, POST: register).
     */
    public function accept(string $token)
    {
        $invite = $this->inviteModel->findValid($token);

        if (! $invite) {
            return view('invite/invalid');
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'username'         => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
                'password'         => 'required|min_length[8]',
                'password_confirm' => 'required|matches[password]',
            ];

            if (! $this->validateData($this->request->getPost(), $rules)) {
                return view('invite/accept', [
                    'token'  => $token,
                    'email'  => $invite['email'],
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $userModel   = new UserModel();
            $optionModel = new UserOptionModel();

            // M7: Atomically claim the invite before inserting the user.
            // If another concurrent request claimed it first, reject immediately.
            if (! $this->inviteModel->atomicMarkUsed($invite['id'])) {
                return view('invite/invalid');
            }

            // Check email is still unclaimed (guard against re-registration with same invite)
            if ($userModel->where('email', $invite['email'])->first()) {
                return view('invite/invalid');
            }

            $userId = $userModel->insert([
                'username'      => $this->request->getPost('username'),
                'email'         => $invite['email'],
                'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'is_approved'   => 1, // invited users skip approval
                'is_active'     => 1,
            ]);

            $optionModel->seedDefaults($userId);

            return redirect()->to('/auth/login')
                             ->with('success', lang('Invite.registrationComplete'));
        }

        return view('invite/accept', [
            'token' => $token,
            'email' => $invite['email'],
        ]);
    }
}
