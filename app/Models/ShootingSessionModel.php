<?php

namespace App\Models;

use CodeIgniter\Model;

class ShootingSessionModel extends Model
{
    protected $table            = 'shooting_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id',
        'weapon_id',
        'location_id',
        'session_date',
        'distance',
        'notes',
    ];

    protected $validationRules = [
        'weapon_id'    => 'required|integer',
        'session_date' => 'required|valid_date[Y-m-d]',
        'distance'     => 'required|max_length[20]',
    ];

    public function getForUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->select('shooting_sessions.*, weapons.name as weapon_name, weapons.type as weapon_type, locations.name as location_name')
                    ->join('weapons', 'weapons.id = shooting_sessions.weapon_id')
                    ->join('locations', 'locations.id = shooting_sessions.location_id', 'left')
                    ->where('shooting_sessions.user_id', $userId)
                    ->orderBy('session_date', 'DESC')
                    ->findAll($limit, $offset);
    }
}
