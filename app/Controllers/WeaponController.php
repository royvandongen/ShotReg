<?php

namespace App\Controllers;

use App\Models\WeaponModel;
use App\Models\UserOptionModel;
use App\Models\UserSettingModel;

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
        $userId = session()->get('user_id');
        $sightings = $this->optionModel->getByType($userId, 'sighting');
        $defaultOwnership = $this->settingModel->getValue($userId, 'default_ownership', 'personal');

        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getPost();
            $data['user_id'] = $userId;

            if (! $this->weaponModel->validate($data)) {
                return view('weapons/form', [
                    'errors'    => $this->weaponModel->errors(),
                    'weapon'    => $data,
                    'action'    => 'create',
                    'sightings' => $sightings,
                ]);
            }

            $this->weaponModel->insert($data);
            return redirect()->to('/weapons')
                             ->with('success', lang('Weapons.added'));
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
            $data = $this->request->getPost();
            $data['user_id'] = $userId;

            if (! $this->weaponModel->validate($data)) {
                return view('weapons/form', [
                    'errors'    => $this->weaponModel->errors(),
                    'weapon'    => $data,
                    'action'    => 'edit',
                    'id'        => $id,
                    'sightings' => $sightings,
                ]);
            }

            $this->weaponModel->update($id, $data);
            return redirect()->to('/weapons')
                             ->with('success', lang('Weapons.updated'));
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
            $this->weaponModel->delete($id);
        }
        return redirect()->to('/weapons')->with('success', lang('Weapons.removed'));
    }

    public function ajaxCreate()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $data = $this->request->getPost();
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
}
