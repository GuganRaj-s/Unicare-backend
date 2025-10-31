<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminAccess extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'nurse_id', 'doctor_id', 'admin_access',  'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
