<?php

namespace App\Controllers;

use App\Models\LocationModel;
use App\Models\UserOptionModel;
use App\Models\UserSettingModel;

class SettingsController extends BaseController
{
    protected LocationModel $locationModel;
    protected UserOptionModel $optionModel;
    protected UserSettingModel $settingModel;

    public function __construct()
    {
        $this->locationModel = new LocationModel();
        $this->optionModel   = new UserOptionModel();
        $this->settingModel  = new UserSettingModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');

        // Seed defaults if user has no options yet
        $existing = $this->optionModel->where('user_id', $userId)->countAllResults();
        if ($existing === 0) {
            $this->optionModel->seedDefaults($userId);
        }

        $locations = $this->locationModel->getForUser($userId);
        $laneTypes = $this->optionModel->getByType($userId, 'lane_type');
        $sightings = $this->optionModel->getByType($userId, 'sighting');
        $defaultOwnership = $this->settingModel->getValue($userId, 'default_ownership', 'personal');

        return view('settings/index', [
            'locations'        => $locations,
            'laneTypes'        => $laneTypes,
            'sightings'        => $sightings,
            'defaultOwnership' => $defaultOwnership,
        ]);
    }

    // --- Locations ---

    public function addLocation()
    {
        $userId = session()->get('user_id');
        $data = $this->request->getPost();
        $data['user_id'] = $userId;

        if (! empty($data['is_default'])) {
            $this->locationModel->clearDefault($userId);
            $data['is_default'] = 1;
        } else {
            $data['is_default'] = 0;
        }

        if (! $this->locationModel->validate($data)) {
            return redirect()->back()
                             ->with('error', implode(', ', $this->locationModel->errors()))
                             ->withInput();
        }

        $this->locationModel->insert($data);
        return redirect()->to('/settings')->with('success', lang('Settings.locationAdded'));
    }

    public function editLocation(int $id)
    {
        $userId   = session()->get('user_id');
        $location = $this->locationModel->find($id);

        if (! $location || $location['user_id'] !== $userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = $this->request->getPost();

        if (! empty($data['is_default'])) {
            $this->locationModel->clearDefault($userId);
            $data['is_default'] = 1;
        } else {
            $data['is_default'] = 0;
        }

        if (! $this->locationModel->validate($data)) {
            return redirect()->back()
                             ->with('error', implode(', ', $this->locationModel->errors()))
                             ->withInput();
        }

        $this->locationModel->update($id, $data);
        return redirect()->to('/settings')->with('success', lang('Settings.locationUpdated'));
    }

    public function deleteLocation(int $id)
    {
        $location = $this->locationModel->find($id);
        if ($location && $location['user_id'] === session()->get('user_id')) {
            $this->locationModel->delete($id);
        }
        return redirect()->to('/settings')->with('success', lang('Settings.locationRemoved'));
    }

    // --- Options (lane types & sighting) ---

    public function addOption()
    {
        $userId = session()->get('user_id');
        $data = $this->request->getPost();
        $data['user_id'] = $userId;

        $type = $data['type'] ?? '';
        if (! in_array($type, ['lane_type', 'sighting'])) {
            return redirect()->back()->with('error', lang('Settings.invalidOptionType'));
        }

        // Auto-set sort_order
        $count = $this->optionModel->where('user_id', $userId)
                                   ->where('type', $type)
                                   ->countAllResults();
        $data['sort_order'] = $count;

        if (! $this->optionModel->validate($data)) {
            return redirect()->back()
                             ->with('error', implode(', ', $this->optionModel->errors()))
                             ->withInput();
        }

        $this->optionModel->insert($data);
        $msg = $type === 'lane_type' ? lang('Settings.laneTypeAdded') : lang('Settings.sightingAdded');
        return redirect()->to('/settings')->with('success', $msg);
    }

    public function deleteOption(int $id)
    {
        $option = $this->optionModel->find($id);
        if ($option && $option['user_id'] === session()->get('user_id')) {
            $this->optionModel->delete($id);
        }
        return redirect()->to('/settings')->with('success', lang('Settings.optionRemoved'));
    }

    // --- Default settings ---

    public function saveDefaults()
    {
        $userId = session()->get('user_id');
        $ownership = $this->request->getPost('default_ownership');

        if (in_array($ownership, ['personal', 'association'])) {
            $this->settingModel->setValue($userId, 'default_ownership', $ownership);
        }

        return redirect()->to('/settings')->with('success', lang('Settings.defaultsSaved'));
    }
}
