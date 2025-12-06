<?php

namespace App\Models;

use CodeIgniter\Model;

class GroupPermissionModel extends Model
{
    protected $table         = 'group_permissions';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['group_name', 'permissions'];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected array $casts = [
        'permissions' => 'json',
    ];

    public function getByGroup(string $group): ?array
    {
        return $this->where('group_name', $group)->first();
    }
}
