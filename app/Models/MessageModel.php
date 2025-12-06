<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table      = 'messages';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'subject',
        'body',
        'sent_by',
        'sent_at',
    ];

    public $useTimestamps = false;
}
