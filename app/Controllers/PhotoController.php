<?php

namespace App\Controllers;

use App\Models\SessionPhotoModel;
use App\Models\ShootingSessionModel;
use App\Models\WeaponModel;

class PhotoController extends BaseController
{
    public function show(string $filename)
    {
        $this->verifyOwnership($filename);

        $path = WRITEPATH . 'uploads/photos/' . basename($filename);
        if (! is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path))
            ->setHeader('Content-Disposition', 'inline')
            ->setBody(file_get_contents($path));
    }

    public function thumbnail(string $filename)
    {
        $this->verifyOwnership($filename, 'thumbnail');

        $path = WRITEPATH . 'uploads/thumbnails/' . basename($filename);
        if (! is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path))
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody(file_get_contents($path));
    }

    public function weaponPhoto(string $filename)
    {
        $this->verifyWeaponOwnership($filename);

        $path = WRITEPATH . 'uploads/weapon_photos/' . basename($filename);
        if (! is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path))
            ->setHeader('Content-Disposition', 'inline')
            ->setBody(file_get_contents($path));
    }

    public function weaponThumbnail(string $filename)
    {
        $this->verifyWeaponOwnership($filename);

        $path = WRITEPATH . 'uploads/weapon_thumbnails/thumb_' . basename($filename);
        if (! is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path))
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody(file_get_contents($path));
    }

    protected function verifyWeaponOwnership(string $filename): void
    {
        $weapon = (new WeaponModel())
            ->where('photo', basename($filename))
            ->where('user_id', session()->get('user_id'))
            ->first();

        if (! $weapon) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    protected function verifyOwnership(string $filename, string $type = 'photo'): void
    {
        $photoModel   = new SessionPhotoModel();
        $sessionModel = new ShootingSessionModel();

        $field = ($type === 'thumbnail') ? 'thumbnail' : 'filename';
        $photo = $photoModel->where($field, basename($filename))->first();

        if (! $photo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $session = $sessionModel->find($photo['shooting_session_id']);
        if (! $session || $session['user_id'] !== session()->get('user_id')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }
}
