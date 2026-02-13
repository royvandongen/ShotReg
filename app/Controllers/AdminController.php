<?php

namespace App\Controllers;

use App\Models\AppSettingModel;
use App\Models\UserModel;
use App\Models\UserOptionModel;

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
            'registrationEnabled' => $this->settingModel->getValue('registration_enabled', '1'),
            'force2fa'            => $this->settingModel->getValue('force_2fa', '0'),
            'defaultLaneTypes'    => $defaultLaneTypes,
            'defaultSightings'    => $defaultSightings,
        ]);
    }

    public function saveSettings()
    {
        $registrationEnabled = $this->request->getPost('registration_enabled') ? '1' : '0';
        $force2fa = $this->request->getPost('force_2fa') ? '1' : '0';

        $this->settingModel->setValue('registration_enabled', $registrationEnabled);
        $this->settingModel->setValue('force_2fa', $force2fa);

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
}
