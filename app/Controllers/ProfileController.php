<?php

namespace App\Controllers;

use App\Models\UserModel;

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

        return redirect()->to('/profile')
                         ->with('success', lang('Profile.passwordChanged'));
    }
}
