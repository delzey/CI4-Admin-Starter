<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageRecipientModel extends Model
{
    protected $table      = 'message_recipients';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'message_id',
        'user_id',
        'folder',
        'is_read',
        'is_deleted',
        'created_at',
    ];

    public $useTimestamps = false;
}
