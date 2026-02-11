<?php

namespace App\Models;

use CodeIgniter\Model;

class AppSettingModel extends Model
{
    protected $table            = 'app_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'setting_key',
        'setting_value',
    ];

    public function getValue(string $key, ?string $default = null): ?string
    {
        $row = $this->where('setting_key', $key)->first();

        return $row ? $row['setting_value'] : $default;
    }

    public function setValue(string $key, ?string $value): void
    {
        $existing = $this->where('setting_key', $key)->first();

        if ($existing) {
            $this->update($existing['id'], ['setting_value' => $value]);
        } else {
            $this->insert([
                'setting_key'   => $key,
                'setting_value' => $value,
            ]);
        }
    }
}
