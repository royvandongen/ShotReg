<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionPhotoModel extends Model
{
    protected $table            = 'session_photos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $updatedField     = '';

    protected $allowedFields = [
        'shooting_session_id',
        'filename',
        'original_name',
        'thumbnail',
        'file_size',
        'sort_order',
    ];

    public function getForSession(int $sessionId): array
    {
        return $this->where('shooting_session_id', $sessionId)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }
}
