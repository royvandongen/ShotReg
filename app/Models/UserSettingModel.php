<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSettingModel extends Model
{
    protected $table            = 'user_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id',
        'setting_key',
        'setting_value',
    ];

    public function getValue(int $userId, string $key, ?string $default = null): ?string
    {
        $row = $this->where('user_id', $userId)
                    ->where('setting_key', $key)
                    ->first();

        return $row ? $row['setting_value'] : $default;
    }

    public function setValue(int $userId, string $key, ?string $value): void
    {
        $existing = $this->where('user_id', $userId)
                         ->where('setting_key', $key)
                         ->first();

        if ($existing) {
            $this->update($existing['id'], ['setting_value' => $value]);
        } else {
            $this->insert([
                'user_id'       => $userId,
                'setting_key'   => $key,
                'setting_value' => $value,
            ]);
        }
    }
}
