<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DashboardModulePermission extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'role_id', 'dashboard_module_id', 'is_permission', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
