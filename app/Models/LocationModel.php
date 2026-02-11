<?php

namespace App\Models;

use CodeIgniter\Model;

class LocationModel extends Model
{
    protected $table            = 'locations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id',
        'name',
        'address',
        'is_default',
    ];

    protected $validationRules = [
        'name' => 'required|max_length[150]',
    ];

    public function getForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    public function getDefault(int $userId): ?array
    {
        return $this->where('user_id', $userId)
                    ->where('is_default', 1)
                    ->first();
    }

    public function clearDefault(int $userId): void
    {
        $this->where('user_id', $userId)
             ->set('is_default', 0)
             ->update();
    }
}
