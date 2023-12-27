<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAuditLog extends Model
{
    protected $connection = 'second_db';
    protected $fillable = [
         'log_type', 'ip_address', 'before_update', 'after_update', 'updated_by', 'user_id', 'updated_at', 'created_at'
    ];
}
