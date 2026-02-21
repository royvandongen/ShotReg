<?php

namespace App\Models;

use CodeIgniter\Model;

class WeaponModel extends Model
{
    protected $table            = 'weapons';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id',
        'name',
        'type',
        'caliber',
        'sighting',
        'ownership',
        'notes',
        'photo',
    ];

    protected $validationRules = [
        'name'      => 'required|max_length[150]',
        'type'      => 'required|in_list[pistol,rifle,shotgun,revolver,other]',
        'caliber'   => 'required|max_length[50]',
        'ownership' => 'required|in_list[personal,association]',
    ];

    public function getForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
}
