<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuCategoryModel extends Model
{
    protected $table            = 'menu_categories';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'menu_category',
        'permission_name',
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules  = [
        'menu_category'  => 'required|string|max_length[100]',
        'permission_name'=> 'permit_empty|string|max_length[255]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
}
