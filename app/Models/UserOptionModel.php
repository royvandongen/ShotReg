<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\AppSettingModel;

class UserOptionModel extends Model
{
    protected $table            = 'user_options';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id',
        'type',
        'label',
        'value',
        'sort_order',
    ];

    protected $validationRules = [
        'type'  => 'required|in_list[lane_type,sighting]',
        'label' => 'required|max_length[100]',
        'value' => 'required|max_length[100]',
    ];

    public function getByType(int $userId, string $type): array
    {
        return $this->where('user_id', $userId)
                    ->where('type', $type)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    public function seedDefaults(int $userId): void
    {
        $appSettings = new AppSettingModel();

        // Check for admin-configured defaults, fall back to built-in defaults
        $adminLaneTypes = json_decode($appSettings->getValue('default_lane_types', '[]'), true) ?: [];
        $adminSightings = json_decode($appSettings->getValue('default_sightings', '[]'), true) ?: [];

        if (! empty($adminLaneTypes)) {
            foreach ($adminLaneTypes as $i => $lt) {
                $this->insert([
                    'user_id'    => $userId,
                    'type'       => 'lane_type',
                    'label'      => $lt['label'],
                    'value'      => strtolower($lt['label']),
                    'sort_order' => $i,
                ]);
            }
        } else {
            $builtInLaneTypes = [
                ['label' => '25m', 'value' => '25m'],
                ['label' => '50m', 'value' => '50m'],
                ['label' => '100m', 'value' => '100m'],
            ];
            foreach ($builtInLaneTypes as $i => $lt) {
                $this->insert([
                    'user_id'    => $userId,
                    'type'       => 'lane_type',
                    'label'      => $lt['label'],
                    'value'      => $lt['value'],
                    'sort_order' => $i,
                ]);
            }
        }

        if (! empty($adminSightings)) {
            foreach ($adminSightings as $i => $s) {
                $this->insert([
                    'user_id'    => $userId,
                    'type'       => 'sighting',
                    'label'      => $s['label'],
                    'value'      => strtolower($s['label']),
                    'sort_order' => $i,
                ]);
            }
        } else {
            $builtInSightings = [
                ['label' => 'Front Sight', 'value' => 'front sight'],
                ['label' => 'Aperture Sight', 'value' => 'aperture sight'],
                ['label' => 'Scope', 'value' => 'scope'],
            ];
            foreach ($builtInSightings as $i => $s) {
                $this->insert([
                    'user_id'    => $userId,
                    'type'       => 'sighting',
                    'label'      => $s['label'],
                    'value'      => $s['value'],
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
