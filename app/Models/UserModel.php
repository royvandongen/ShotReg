<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'username',
        'email',
        'first_name',
        'last_name',
        'knsa_member_id',
        'password_hash',
        'is_admin',
        'totp_secret',
        'totp_enabled',
        'totp_last_timestamp',
    ];

    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
    ];

    protected $validationMessages = [
        'username' => [
            'is_unique' => 'This username is already taken.',
        ],
        'email' => [
            'is_unique' => 'This email is already registered.',
        ],
    ];

}
