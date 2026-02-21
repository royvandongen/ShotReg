<?php

namespace App\Controllers;

use App\Models\WeaponModel;
use App\Models\UserOptionModel;
use App\Models\UserSettingModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class WeaponController extends BaseController
{
    protected WeaponModel $weaponModel;
    protected UserOptionModel $optionModel;
    protected UserSettingModel $settingModel;

    public function __construct()
    {
        $this->weaponModel  = new WeaponModel();
        $this->optionModel  = new UserOptionModel();
        $this->settingModel = new UserSettingModel();
    }

    public function index()
    {
        $weapons = $this->weaponModel->getForUser(session()->get('user_id'));
        return view('weapons/index', ['weapons' => $weapons]);
    }

    public function create()
    {
        $userId           = session()->get('user_id');
        $sightings        = $this->optionModel->getByType($userId, 'sighting');
        $defaultOwnership = $this->settingModel->getValue($userId, 'default_ownership', 'personal');

        if ($this->request->getMethod() === 'POST') {
            $data            = $this->request->getPost();
            $data['user_id'] = $userId;

            if (! $this->weaponModel->validate($data)) {
                return view('weapons/form', [
                    'errors'    => $this->weaponModel->errors(),
                    'weapon'    => $data,
                    'action'    => 'create',
                    'sightings' => $sightings,
                ]);
            }

            $id       = $this->weaponModel->insert($data);
            $filename = $this->handleWeaponPhotoUpload($this->request->getFile('weapon_photo'));
            if ($filename !== null) {
                $this->weaponModel->update($id, ['photo' => $filename]);
            }

            return redirect()->to('/weapons')->with('success', lang('Weapons.added'));
        }

        return view('weapons/form', [
            'action'    => 'create',
            'weapon'    => ['ownership' => $defaultOwnership],
            'sightings' => $sightings,
        ]);
    }

    public function edit(int $id)
    {
        $userId = session()->get('user_id');
        $weapon = $this->weaponModel->find($id);

        if (! $weapon || $weapon['user_id'] !== $userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $sightings = $this->optionModel->getByType($userId, 'sighting');

        if ($this->request->getMethod() === 'POST') {
            $data            = $this->request->getPost();
            $data['user_id'] = $userId;

            if (! $this->weaponModel->validate($data)) {
                return view('weapons/form', [
                    'errors'    => $this->weaponModel->errors(),
                    'weapon'    => array_merge($weapon, $data),
                    'action'    => 'edit',
                    'id'        => $id,
                    'sightings' => $sightings,
                ]);
            }

            $file        = $this->request->getFile('weapon_photo');
            $removePhoto = (bool) $this->request->getPost('remove_photo');

            if ($file && $file->isValid() && ! $file->hasMoved()) {
                // New photo uploaded — delete old one first, then store new
                $this->deleteWeaponPhotoFiles($weapon['photo'] ?? null);
                $data['photo'] = $this->handleWeaponPhotoUpload($file);
            } elseif ($removePhoto) {
                // User explicitly requested removal
                $this->deleteWeaponPhotoFiles($weapon['photo'] ?? null);
                $data['photo'] = null;
            }

            $this->weaponModel->update($id, $data);
            return redirect()->to('/weapons')->with('success', lang('Weapons.updated'));
        }

        return view('weapons/form', [
            'weapon'    => $weapon,
            'action'    => 'edit',
            'id'        => $id,
            'sightings' => $sightings,
        ]);
    }

    public function delete(int $id)
    {
        $weapon = $this->weaponModel->find($id);
        if ($weapon && $weapon['user_id'] === session()->get('user_id')) {
            $this->deleteWeaponPhotoFiles($weapon['photo'] ?? null);
            $this->weaponModel->delete($id);
        }
        return redirect()->to('/weapons')->with('success', lang('Weapons.removed'));
    }

    public function ajaxCreate()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $data            = $this->request->getPost();
        $data['user_id'] = session()->get('user_id');

        if (! $this->weaponModel->validate($data)) {
            return $this->response->setJSON([
                'success'    => false,
                'errors'     => $this->weaponModel->errors(),
                'csrf_token' => csrf_hash(),
            ]);
        }

        $id = $this->weaponModel->insert($data);

        return $this->response->setJSON([
            'success'    => true,
            'weapon'     => ['id' => $id, 'name' => $data['name']],
            'csrf_token' => csrf_hash(),
        ]);
    }

    /**
     * Upload a weapon photo, generate a 300x300 thumbnail.
     * Returns the stored filename on success, null if no valid file was provided.
     */
    private function handleWeaponPhotoUpload(?UploadedFile $file): ?string
    {
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($file->getMimeType(), $allowed, true)) {
            return null;
        }

        if ($file->getSizeByUnit('mb') > 5) {
            return null;
        }

        $filename = $file->getRandomName();
        $photoDir = WRITEPATH . 'uploads/weapon_photos/';
        $thumbDir = WRITEPATH . 'uploads/weapon_thumbnails/';

        if (! is_dir($photoDir)) {
            mkdir($photoDir, 0755, true);
        }
        if (! is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        $file->move($photoDir, $filename);

        service('image')
            ->withFile($photoDir . $filename)
            ->fit(300, 300, 'center')
            ->save($thumbDir . 'thumb_' . $filename, 80);

        return $filename;
    }

    /**
     * Delete weapon photo and thumbnail files from disk.
     */
    private function deleteWeaponPhotoFiles(?string $filename): void
    {
        if (! $filename) {
            return;
        }

        $photo = WRITEPATH . 'uploads/weapon_photos/' . basename($filename);
        $thumb = WRITEPATH . 'uploads/weapon_thumbnails/thumb_' . basename($filename);

        if (is_file($photo)) {
            unlink($photo);
        }
        if (is_file($thumb)) {
            unlink($thumb);
        }
    }
}
