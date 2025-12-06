<?php

namespace App\Models;

use CodeIgniter\Model;

class DbBackupLogModel extends Model
{
    protected $table = 'db_backups';
    protected $primaryKey = 'id';
    protected $allowedFields = ['file_path', 'label', 'user_id', 'created_at'];
    public $timestamps = false;
}
