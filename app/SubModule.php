<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubModule extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'main_module_id', 'menu_order', 'menu_icon', 'menu_link', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
