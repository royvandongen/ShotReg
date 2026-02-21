<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\LocationModel;
use App\Models\SessionPhotoModel;
use App\Models\ShootingSessionModel;
use App\Models\UserModel;
use App\Models\UserOptionModel;
use App\Models\UserSettingModel;
use App\Models\UserTokenModel;
use App\Models\WeaponModel;

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

    public function exportData()
    {
        set_time_limit(120);

        $userId = (int) session()->get('user_id');
        $user   = $this->userModel->find($userId);

        if (! $user) {
            return redirect()->to('/auth/login');
        }

        // Fetch all user data
        $weapons   = (new WeaponModel())->getForUser($userId);
        $locations = (new LocationModel())->getForUser($userId);
        $sessions  = (new ShootingSessionModel())->getForUser($userId, 0); // 0 = all rows
        $photoModel = new SessionPhotoModel();

        // User settings (key-value)
        $settingRows = (new UserSettingModel())->where('user_id', $userId)->findAll();
        $settings    = [];
        foreach ($settingRows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        // User options (lane types and sightings)
        $optionModel = new UserOptionModel();
        $options = [
            'lane_types' => array_map(
                fn($o) => ['label' => $o['label'], 'value' => $o['value']],
                $optionModel->getByType($userId, 'lane_type')
            ),
            'sightings' => array_map(
                fn($o) => ['label' => $o['label'], 'value' => $o['value']],
                $optionModel->getByType($userId, 'sighting')
            ),
        ];

        // Create temp ZIP file
        $tmpFile = tempnam(sys_get_temp_dir(), 'shotreg_export_');
        $zip     = new \ZipArchive();

        if ($zip->open($tmpFile, \ZipArchive::OVERWRITE) !== true) {
            log_message('error', 'Export: could not create ZIP for user {id}', ['id' => $userId]);
            return redirect()->to('/profile')->with('error', lang('Profile.exportFailed'));
        }

        // Build JSON structure
        $jsonData = [
            'exported_at' => date('c'),
            'profile'     => [
                'username'       => $user['username'],
                'email'          => $user['email'],
                'first_name'     => $user['first_name'] ?? null,
                'last_name'      => $user['last_name'] ?? null,
                'knsa_member_id' => $user['knsa_member_id'] ?? null,
            ],
            'settings'  => $settings,
            'options'   => $options,
            'locations' => array_map(fn($loc) => [
                'name'       => $loc['name'],
                'address'    => $loc['address'],
                'is_default' => (bool) $loc['is_default'],
            ], $locations),
            'weapons'  => [],
            'sessions' => [],
        ];

        // Weapons + weapon photos
        foreach ($weapons as $weapon) {
            $safeFolder = 'weapons/' . $weapon['id'] . '_' . $this->safeName($weapon['name']) . '/';
            $photoInZip = null;

            if (! empty($weapon['photo'])) {
                $src = WRITEPATH . 'uploads/weapon_photos/' . basename($weapon['photo']);
                if (is_file($src)) {
                    $ext        = pathinfo($weapon['photo'], PATHINFO_EXTENSION);
                    $photoInZip = $safeFolder . 'photo.' . $ext;
                    $zip->addFile($src, $photoInZip);
                } else {
                    log_message('warning', 'Export: weapon photo missing: {f}', ['f' => $src]);
                }
            }

            $jsonData['weapons'][] = [
                'id'        => (int) $weapon['id'],
                'name'      => $weapon['name'],
                'type'      => $weapon['type'],
                'caliber'   => $weapon['caliber'],
                'sighting'  => $weapon['sighting'],
                'ownership' => $weapon['ownership'],
                'notes'     => $weapon['notes'],
                'photo'     => $photoInZip,
            ];
        }

        // Sessions + session photos
        foreach ($sessions as $session) {
            $safeFolder = 'sessions/' . $session['id'] . '_' . $session['session_date'] . '/';
            $photos     = $photoModel->getForSession((int) $session['id']);
            $nameCounts = [];
            $photoData  = [];

            foreach ($photos as $photo) {
                $src      = WRITEPATH . 'uploads/photos/' . basename($photo['filename']);
                $origName = basename($photo['original_name']);

                // Deduplicate filenames within the same folder
                if (isset($nameCounts[$origName])) {
                    $nameCounts[$origName]++;
                    $ext      = pathinfo($origName, PATHINFO_EXTENSION);
                    $base     = pathinfo($origName, PATHINFO_FILENAME);
                    $origName = $base . '_' . $nameCounts[$origName] . '.' . $ext;
                } else {
                    $nameCounts[$origName] = 1;
                }

                $zipName = $safeFolder . $origName;

                if (is_file($src)) {
                    $zip->addFile($src, $zipName);
                } else {
                    log_message('warning', 'Export: session photo missing: {f}', ['f' => $src]);
                }

                $photoData[] = [
                    'original_name' => $photo['original_name'],
                    'path_in_zip'   => $zipName,
                ];
            }

            $jsonData['sessions'][] = [
                'id'          => (int) $session['id'],
                'date'        => $session['session_date'],
                'weapon'      => $session['weapon_name'],
                'weapon_type' => $session['weapon_type'],
                'location'    => $session['location_name'],
                'distance'    => $session['distance'],
                'notes'       => $session['notes'],
                'photos'      => $photoData,
            ];
        }

        $zip->addFromString('data.json', json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->close();

        // Stream to client
        $downloadName = 'shotreg_export_' . $this->safeName($user['username']) . '.zip';

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: no-cache, no-store, must-revalidate');

        readfile($tmpFile);
        unlink($tmpFile);
        exit;
    }

    /**
     * Strip characters unsafe for ZIP entry names / filenames.
     */
    private function safeName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
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
     * Sign out a single remembered session by token ID.
     * F17: Uses the numeric DB id instead of the selector to avoid exposing the selector in URLs.
     */
    public function revokeSession(int $tokenId)
    {
        $userId     = (int) session()->get('user_id');
        $tokenModel = new UserTokenModel();

        // Verify the token belongs to this user (ownership check via user_id + id)
        $token = $tokenModel->where('id', $tokenId)
                            ->where('user_id', $userId)
                            ->first();

        if ($token) {
            $tokenModel->revokeBySelector($token['selector']);

            // If revoking the current remember-me session, clear the cookie too
            if (session()->get('remember_selector') === $token['selector']) {
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
