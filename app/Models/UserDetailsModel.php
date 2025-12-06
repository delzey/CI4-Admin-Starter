<?php

namespace App\Models;

use CodeIgniter\Model;

class UserDetailsModel extends Model
{
    protected $table            = 'user_details';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'user_id',
        'firstname',
        'middlename',
        'lastname',
        'phone',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'profile_complete',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Convenience: get details for user_id or null if none.
     */
    public function forUser(int $userId): ?object
    {
        return $this->where('user_id', $userId)->first();
    }
}
