<?php

namespace App\Controllers;

use App\Models\ShootingSessionModel;
use App\Models\WeaponModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $userId = session()->get('user_id');
        $sessionModel = new ShootingSessionModel();
        $weaponModel  = new WeaponModel();

        return view('dashboard/index', [
            'recentSessions' => $sessionModel->getForUser($userId, 5),
            'weaponCount'    => count($weaponModel->getForUser($userId)),
            'sessionCount'   => $sessionModel->where('user_id', $userId)->countAllResults(),
        ]);
    }
}
