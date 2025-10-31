<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'request_from', 'request_data', 'request_ip', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
