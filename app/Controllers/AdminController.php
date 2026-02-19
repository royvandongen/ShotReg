<?php

namespace App\Controllers;

use App\Libraries\Mailer;
use App\Models\AppSettingModel;
use App\Models\InviteModel;
use App\Models\UserModel;
use App\Models\UserOptionModel;
use App\Models\UserSettingModel;

class AdminController extends BaseController
{
    protected AppSettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new AppSettingModel();
    }

    public function settings()
    {
        $defaultLaneTypes = json_decode($this->settingModel->getValue('default_lane_types', '[]'), true) ?: [];
        $defaultSightings = json_decode($this->settingModel->getValue('default_sightings', '[]'), true) ?: [];

        return view('admin/settings', [
            'registrationEnabled'  => $this->settingModel->getValue('registration_enabled', '1'),
            'force2fa'             => $this->settingModel->getValue('force_2fa', '0'),
            'invitesEnabled'       => $this->settingModel->getValue('invites_enabled', '0'),
            'userInvitesEnabled'   => $this->settingModel->getValue('user_invites_enabled', '0'),
            'userInviteLimit'      => $this->settingModel->getValue('user_invite_limit', '5'),
            'resetExpiryMinutes'   => $this->settingModel->getValue('password_reset_expiry_minutes', '60'),
            'defaultLaneTypes'     => $defaultLaneTypes,
            'defaultSightings'     => $defaultSightings,
        ]);
    }

    public function saveSettings()
    {
        $this->settingModel->setValue('registration_enabled', $this->request->getPost('registration_enabled') ? '1' : '0');
        $this->settingModel->setValue('force_2fa',            $this->request->getPost('force_2fa') ? '1' : '0');
        $this->settingModel->setValue('invites_enabled',      $this->request->getPost('invites_enabled') ? '1' : '0');
        $this->settingModel->setValue('user_invites_enabled', $this->request->getPost('user_invites_enabled') ? '1' : '0');

        $limit = max(0, (int) $this->request->getPost('user_invite_limit'));
        $this->settingModel->setValue('user_invite_limit', (string) $limit);

        $expiry = max(1, (int) $this->request->getPost('password_reset_expiry_minutes'));
        $this->settingModel->setValue('password_reset_expiry_minutes', (string) $expiry);

        return redirect()->to('/admin/settings')
                         ->with('success', lang('Admin.settingsSaved'));
    }

    public function addDefault()
    {
        $type  = $this->request->getPost('type');
        $label = trim($this->request->getPost('label'));

        if (! in_array($type, ['lane_type', 'sighting'], true) || $label === '') {
            return redirect()->to('/admin/settings')->with('error', lang('Admin.invalidInput'));
        }

        $settingKey = $type === 'lane_type' ? 'default_lane_types' : 'default_sightings';
        $existing = json_decode($this->settingModel->getValue($settingKey, '[]'), true) ?: [];

        $existing[] = ['label' => $label, 'value' => strtolower($label)];
        $this->settingModel->setValue($settingKey, json_encode($existing));

        return redirect()->to('/admin/settings')->with('success', lang('Admin.defaultAdded'));
    }

    public function deleteDefault()
    {
        $type  = $this->request->getPost('type');
        $index = (int) $this->request->getPost('index');

        if (! in_array($type, ['lane_type', 'sighting'], true)) {
            return redirect()->to('/admin/settings')->with('error', lang('Admin.invalidType'));
        }

        $settingKey = $type === 'lane_type' ? 'default_lane_types' : 'default_sightings';
        $existing = json_decode($this->settingModel->getValue($settingKey, '[]'), true) ?: [];

        if (isset($existing[$index])) {
            array_splice($existing, $index, 1);
            $this->settingModel->setValue($settingKey, json_encode($existing));
        }

        return redirect()->to('/admin/settings')->with('success', lang('Admin.defaultRemoved'));
    }

    public function users()
    {
        $db = \Config\Database::connect();
        $search = $this->request->getGet('q') ?? '';

        $builder = $db->table('users')
            ->select('users.id, users.username, users.email, users.is_admin, users.is_approved, users.is_active, users.totp_enabled, users.created_at')
            ->select('(SELECT COUNT(*) FROM weapons WHERE weapons.user_id = users.id) AS weapon_count')
            ->select('(SELECT COUNT(*) FROM shooting_sessions WHERE shooting_sessions.user_id = users.id) AS session_count')
            ->select('(SELECT COUNT(*) FROM locations WHERE locations.user_id = users.id) AS location_count');

        if ($search !== '') {
            $builder->groupStart()
                    ->like('users.username', $search)
                    ->orLike('users.email', $search)
                    ->groupEnd();
        }

        $users = $builder->orderBy('users.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/users', [
            'users'  => $users,
            'search' => $search,
        ]);
    }

    public function toggleAdmin(int $userId)
    {
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (! $user) {
            return redirect()->to('/admin/users')
                             ->with('error', lang('Admin.userNotFound'));
        }

        // Prevent demoting yourself
        if ($userId === (int) session()->get('user_id')) {
            return redirect()->to('/admin/users')
                             ->with('error', lang('Admin.cannotChangeOwnRole'));
        }

        $newStatus = $user['is_admin'] ? 0 : 1;
        $userModel->update($userId, ['is_admin' => $newStatus]);

        $msg = $newStatus
            ? lang('Admin.userPromoted', [esc($user['username'])])
            : lang('Admin.userDemoted', [esc($user['username'])]);

        return redirect()->to('/admin/users')
                         ->with('success', $msg);
    }

    public function approveUser(int $userId)
    {
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (! $user) {
            return redirect()->to('/admin/users')
                             ->with('error', lang('Admin.userNotFound'));
        }

        $userModel->update($userId, ['is_approved' => 1]);

        return redirect()->to('/admin/users')
                         ->with('success', lang('Admin.userApproved', [esc($user['username'])]));
    }

    public function rejectUser(int $userId)
    {
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (! $user) {
            return redirect()->to('/admin/users')
                             ->with('error', lang('Admin.userNotFound'));
        }

        // Prevent rejecting yourself
        if ($userId === (int) session()->get('user_id')) {
            return redirect()->to('/admin/users')
                             ->with('error', lang('Admin.cannotRejectSelf'));
        }

        $userModel->delete($userId);

        return redirect()->to('/admin/users')
                         ->with('success', lang('Admin.userRejected', [esc($user['username'])]));
    }

    public function toggleActive(int $userId)
    {
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (! $user) {
            return redirect()->to('/admin/users')
                             ->with('error', lang('Admin.userNotFound'));
        }

        // Prevent disabling yourself
        if ($userId === (int) session()->get('user_id')) {
            return redirect()->to('/admin/users')
                             ->with('error', lang('Admin.cannotDisableSelf'));
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $userModel->update($userId, ['is_active' => $newStatus]);

        $msg = $newStatus
            ? lang('Admin.userEnabled', [esc($user['username'])])
            : lang('Admin.userDisabled', [esc($user['username'])]);

        return redirect()->to('/admin/users')
                         ->with('success', $msg);
    }

    // -------------------------------------------------------------------------
    // Email Settings
    // -------------------------------------------------------------------------

    public function emailSettings()
    {
        $mailer = new Mailer();

        return view('admin/email', [
            'emailProtocol'        => $this->settingModel->getValue('email_protocol', 'smtp'),
            'smtpHost'             => $this->settingModel->getValue('smtp_host', ''),
            'smtpPort'             => $this->settingModel->getValue('smtp_port', '587'),
            'smtpUser'             => $this->settingModel->getValue('smtp_user', ''),
            'smtpPass'             => $this->settingModel->getValue('smtp_pass', ''),
            'smtpCrypto'           => $this->settingModel->getValue('smtp_crypto', 'tls'),
            'emailFromAddress'     => $this->settingModel->getValue('email_from_address', ''),
            'emailFromName'        => $this->settingModel->getValue('email_from_name', 'Shotr'),
            'templateInvite'       => $this->settingModel->getValue('email_template_invite') ?: $mailer->defaultInviteTemplate(),
            'templateReset'        => $this->settingModel->getValue('email_template_reset') ?: $mailer->defaultResetTemplate(),
        ]);
    }

    public function saveEmailSettings()
    {
        $postMap = [
            'email_protocol'    => 'email_protocol',
            'smtp_host'         => 'smtp_host',
            'smtp_port'         => 'smtp_port',
            'smtp_user'         => 'smtp_user',
            'smtp_crypto'       => 'smtp_crypto',
            'email_from_address' => 'email_from_address',
            'email_from_name'   => 'email_from_name',
        ];

        foreach ($postMap as $postKey => $settingKey) {
            $this->settingModel->setValue($settingKey, trim($this->request->getPost($postKey) ?? ''));
        }

        // Only update password if provided (don't wipe existing)
        $newPass = $this->request->getPost('smtp_pass');
        if ($newPass !== '' && $newPass !== null) {
            $this->settingModel->setValue('smtp_pass', $newPass);
        }

        return redirect()->to('/admin/email')
                         ->with('success', lang('Admin.emailSettingsSaved'));
    }

    public function testEmail()
    {
        $adminUser  = (new UserModel())->find((int) session()->get('user_id'));
        $mailer     = new Mailer();

        if (! $mailer->isConfigured()) {
            return redirect()->to('/admin/email')
                             ->with('error', lang('Admin.emailNotConfigured'));
        }

        $sent = $mailer->sendTest($adminUser['email']);

        return redirect()->to('/admin/email')
                         ->with($sent ? 'success' : 'error', $sent ? lang('Admin.testEmailSent', [$adminUser['email']]) : lang('Admin.testEmailFailed'));
    }

    public function saveEmailTemplate(string $type)
    {
        $allowed = ['invite', 'reset'];
        if (! in_array($type, $allowed, true)) {
            return redirect()->to('/admin/email')->with('error', lang('Admin.invalidInput'));
        }

        $key  = 'email_template_' . $type;
        $html = $this->request->getPost('template') ?? '';
        $this->settingModel->setValue($key, $html);

        return redirect()->to('/admin/email')
                         ->with('success', lang('Admin.templateSaved'));
    }

    // -------------------------------------------------------------------------
    // Invite Management
    // -------------------------------------------------------------------------

    public function invites()
    {
        $inviteModel = new InviteModel();
        $invites     = $inviteModel->getAllWithSenders();

        // Build per-sender usage summary
        $senderStats = [];
        $userSettingModel = new UserSettingModel();
        foreach ($invites as $inv) {
            $senderId = $inv['invited_by'];
            if ($senderId && ! isset($senderStats[$senderId])) {
                $override = $userSettingModel->getValue((int) $senderId, 'invite_limit_override');
                $globalLimit = (int) $this->settingModel->getValue('user_invite_limit', '5');
                $senderStats[$senderId] = [
                    'override'     => $override,
                    'global_limit' => $globalLimit,
                ];
            }
        }

        return view('admin/invites', [
            'invites'     => $invites,
            'senderStats' => $senderStats,
        ]);
    }

    public function sendInvite()
    {
        $email = trim($this->request->getPost('invite_email') ?? '');

        if (! $this->validateData(['email' => $email], ['email' => 'required|valid_email'])) {
            return redirect()->to('/admin/invites')->with('error', lang('Invite.invalidEmail'));
        }

        $userModel = new UserModel();
        if ($userModel->where('email', $email)->first()) {
            return redirect()->to('/admin/invites')->with('error', lang('Invite.emailAlreadyRegistered'));
        }

        $inviteModel = new InviteModel();
        $invite      = $inviteModel->createInvite($email, (int) session()->get('user_id'));
        $baseUrl     = rtrim(config('App')->baseURL, '/');
        $inviteLink  = $baseUrl . '/invite/' . $invite['token'];

        $mailer    = new Mailer();
        $emailSent = false;
        if ($mailer->isConfigured()) {
            $username  = session()->get('username') ?? 'Admin';
            $emailSent = $mailer->sendInvite($email, $username, $invite['token']);
        }

        $msg = $emailSent
            ? lang('Invite.inviteSentWithEmail', [$email])
            : lang('Invite.inviteCreated');

        return redirect()->to('/admin/invites')
                         ->with('success', $msg)
                         ->with('invite_link', $inviteLink);
    }

    public function setUserInviteLimit(int $userId)
    {
        $userModel = new UserModel();
        if (! $userModel->find($userId)) {
            return redirect()->to('/admin/invites')->with('error', lang('Admin.userNotFound'));
        }

        $limit = $this->request->getPost('invite_limit');

        $userSettingModel = new UserSettingModel();
        if ($limit === '' || $limit === null) {
            // Remove override — revert to global limit
            $existing = $userSettingModel->where('user_id', $userId)
                                         ->where('setting_key', 'invite_limit_override')
                                         ->first();
            if ($existing) {
                $userSettingModel->delete($existing['id']);
            }
        } else {
            $userSettingModel->setValue($userId, 'invite_limit_override', (string) max(0, (int) $limit));
        }

        return redirect()->to('/admin/invites')
                         ->with('success', lang('Admin.inviteLimitUpdated'));
    }

    public function revokeUserInvites(int $userId)
    {
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (! $user) {
            return redirect()->to('/admin/invites')->with('error', lang('Admin.userNotFound'));
        }

        $inviteModel = new InviteModel();
        $count = $inviteModel->revokeByUser($userId);

        return redirect()->to('/admin/invites')
                         ->with('success', lang('Admin.invitesRevoked', [$count, esc($user['username'])]));
    }
}
