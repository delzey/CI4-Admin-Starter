<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table            = 'menus';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'category_id',
        'parent_id',
        'title',
        'icon',
        'route',
        'permission',
        'position',
        'is_active',
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules  = [
        'title'      => 'required|string|max_length[100]',
        'parent_id'  => 'permit_empty|integer',
        'icon'       => 'permit_empty|string|max_length[50]',
        'route'      => 'permit_empty|string|max_length[255]',
        'permission' => 'permit_empty|string|max_length[100]',
        'position'   => 'permit_empty|integer',
        'is_active'  => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
}
