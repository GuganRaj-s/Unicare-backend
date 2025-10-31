<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubModulePermission extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'role_id', 'main_module_id', 'sub_module_id', 'is_permission', 'is_add', 'is_edit', 'is_view', 'is_delete', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
