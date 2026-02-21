<?php

namespace App\Controllers;

use App\Models\ShootingSessionModel;
use App\Models\SessionPhotoModel;
use App\Models\WeaponModel;
use App\Models\LocationModel;
use App\Models\UserOptionModel;
use App\Models\UserSettingModel;

class SessionController extends BaseController
{
    protected ShootingSessionModel $sessionModel;
    protected SessionPhotoModel $photoModel;
    protected WeaponModel $weaponModel;
    protected LocationModel $locationModel;
    protected UserOptionModel $optionModel;
    protected UserSettingModel $settingModel;

    public function __construct()
    {
        $this->sessionModel  = new ShootingSessionModel();
        $this->photoModel    = new SessionPhotoModel();
        $this->weaponModel   = new WeaponModel();
        $this->locationModel = new LocationModel();
        $this->optionModel   = new UserOptionModel();
        $this->settingModel  = new UserSettingModel();
    }

    protected function getFormOptions(int $userId): array
    {
        // Seed defaults if user has no options yet
        $existing = $this->optionModel->where('user_id', $userId)->countAllResults();
        if ($existing === 0) {
            $this->optionModel->seedDefaults($userId);
        }

        return [
            'locations' => $this->locationModel->getForUser($userId),
            'laneTypes' => $this->optionModel->getByType($userId, 'lane_type'),
            'sightings' => $this->optionModel->getByType($userId, 'sighting'),
        ];
    }

    public function index()
    {
        $userId   = session()->get('user_id');
        $sessions = $this->sessionModel->getForUser($userId);

        foreach ($sessions as &$s) {
            $photos = $this->photoModel->getForSession($s['id']);
            $s['photo_count'] = count($photos);
            $s['first_thumb'] = $photos[0]['thumbnail'] ?? null;
        }

        return view('sessions/index', ['sessions' => $sessions]);
    }

    public function create()
    {
        $userId  = session()->get('user_id');
        $weapons = $this->weaponModel->getForUser($userId);
        $options = $this->getFormOptions($userId);

        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getPost();
            $data['user_id'] = $userId;
            $data['location_id'] = !empty($data['location_id']) ? $data['location_id'] : null;

            if (! $this->sessionModel->validate($data)) {
                return view('sessions/form', array_merge([
                    'errors'  => $this->sessionModel->errors(),
                    'session' => $data,
                    'weapons' => $weapons,
                    'action'  => 'create',
                ], $options));
            }

            $sessionId = $this->sessionModel->insert($data);
            $this->handlePhotoUploads($sessionId);

            return redirect()->to('/sessions')
                             ->with('success', lang('Sessions.recorded'));
        }

        // Get default location
        $defaultLocation = $this->locationModel->getDefault($userId);

        return view('sessions/form', array_merge([
            'weapons' => $weapons,
            'action'  => 'create',
            'session' => [
                'session_date' => date('Y-m-d'),
                'location_id'  => $defaultLocation['id'] ?? '',
            ],
        ], $options));
    }

    public function edit(int $id)
    {
        $userId  = session()->get('user_id');
        $session = $this->sessionModel->find($id);

        if (! $session || $session['user_id'] !== $userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $weapons = $this->weaponModel->getForUser($userId);
        $photos  = $this->photoModel->getForSession($id);
        $options = $this->getFormOptions($userId);

        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getPost();
            $data['user_id'] = $userId;
            $data['location_id'] = !empty($data['location_id']) ? $data['location_id'] : null;

            if (! $this->sessionModel->validate($data)) {
                return view('sessions/form', array_merge([
                    'errors'  => $this->sessionModel->errors(),
                    'session' => $data,
                    'weapons' => $weapons,
                    'photos'  => $photos,
                    'action'  => 'edit',
                    'id'      => $id,
                ], $options));
            }

            $this->sessionModel->update($id, $data);
            $this->handlePhotoUploads($id);

            return redirect()->to('/sessions/' . $id)
                             ->with('success', lang('Sessions.updated'));
        }

        return view('sessions/form', array_merge([
            'session' => $session,
            'weapons' => $weapons,
            'photos'  => $photos,
            'action'  => 'edit',
            'id'      => $id,
        ], $options));
    }

    public function show(int $id)
    {
        $userId  = session()->get('user_id');
        $session = $this->sessionModel
            ->select('shooting_sessions.*, weapons.name as weapon_name, weapons.type as weapon_type, weapons.caliber, locations.name as location_name, locations.address as location_address')
            ->join('weapons', 'weapons.id = shooting_sessions.weapon_id')
            ->join('locations', 'locations.id = shooting_sessions.location_id', 'left')
            ->find($id);

        if (! $session || $session['user_id'] !== $userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $photos = $this->photoModel->getForSession($id);

        return view('sessions/show', [
            'session' => $session,
            'photos'  => $photos,
        ]);
    }

    public function delete(int $id)
    {
        $session = $this->sessionModel->find($id);
        if ($session && $session['user_id'] === session()->get('user_id')) {
            $photos = $this->photoModel->getForSession($id);
            foreach ($photos as $photo) {
                $this->deletePhotoFiles($photo);
            }
            $this->sessionModel->delete($id);
        }
        return redirect()->to('/sessions')->with('success', lang('Sessions.deleted'));
    }

    public function deletePhoto(int $photoId)
    {
        $photo = $this->photoModel->find($photoId);
        if ($photo) {
            $session = $this->sessionModel->find($photo['shooting_session_id']);
            if ($session && $session['user_id'] === session()->get('user_id')) {
                $this->deletePhotoFiles($photo);
                $this->photoModel->delete($photoId);
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'csrf_token' => csrf_hash()]);
        }

        return redirect()->back()->with('success', lang('Sessions.photoRemoved'));
    }

    public function ajaxCreateLocation()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $userId = session()->get('user_id');
        $data = $this->request->getPost();
        $data['user_id'] = $userId;
        $data['is_default'] = 0;

        if (! $this->locationModel->validate($data)) {
            return $this->response->setJSON([
                'success'    => false,
                'errors'     => $this->locationModel->errors(),
                'csrf_token' => csrf_hash(),
            ]);
        }

        $id = $this->locationModel->insert($data);

        return $this->response->setJSON([
            'success'    => true,
            'location'   => [
                'id'   => $id,
                'name' => $data['name'],
            ],
            'csrf_token' => csrf_hash(),
        ]);
    }

    public function reorderPhotos()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $photoIds = $this->request->getPost('photo_ids');
        if (! is_array($photoIds)) {
            return $this->response->setJSON(['success' => false, 'csrf_token' => csrf_hash()]);
        }

        $userId = session()->get('user_id');

        foreach ($photoIds as $index => $photoId) {
            $photo = $this->photoModel->find((int) $photoId);
            if (! $photo) {
                continue;
            }
            $session = $this->sessionModel->find($photo['shooting_session_id']);
            if ($session && $session['user_id'] === $userId) {
                $this->photoModel->update((int) $photoId, ['sort_order' => $index]);
            }
        }

        return $this->response->setJSON(['success' => true, 'csrf_token' => csrf_hash()]);
    }

    protected function handlePhotoUploads(int $sessionId): void
    {
        $files = $this->request->getFileMultiple('photos');

        if (! $files) {
            return;
        }

        $photoDir = WRITEPATH . 'uploads/photos/';
        $thumbDir = WRITEPATH . 'uploads/thumbnails/';
        if (! is_dir($photoDir)) {
            mkdir($photoDir, 0755, true);
        }
        if (! is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        // Get current max sort_order for this session
        $existing = $this->photoModel->getForSession($sessionId);
        $nextOrder = count($existing);

        foreach ($files as $file) {
            if (! $file->isValid() || $file->hasMoved()) {
                continue;
            }

            if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
                continue;
            }

            try {
                $newName = $file->getRandomName();
                $file->move($photoDir, $newName);

                $thumbName = 'thumb_' . $newName;
                service('image')
                    ->withFile($photoDir . $newName)
                    ->fit(300, 300, 'center')
                    ->save($thumbDir . $thumbName, 80);

                $this->photoModel->insert([
                    'shooting_session_id' => $sessionId,
                    'filename'            => $newName,
                    'original_name'       => $file->getClientName(),
                    'thumbnail'           => $thumbName,
                    'file_size'           => $file->getSize(),
                    'sort_order'          => $nextOrder++,
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Photo upload failed for session {session}: {message}', [
                    'session' => $sessionId,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function deletePhotoFiles(array $photo): void
    {
        $photoPath = WRITEPATH . 'uploads/photos/' . $photo['filename'];
        $thumbPath = WRITEPATH . 'uploads/thumbnails/' . $photo['thumbnail'];

        if (is_file($photoPath)) {
            unlink($photoPath);
        }
        if (is_file($thumbPath)) {
            unlink($thumbPath);
        }
    }
}
