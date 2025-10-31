<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MainModulePermission extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'role_id', 'main_module_id', 'is_permission', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
