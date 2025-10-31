<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MainModule extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'menu_order', 'is_main_menu', 'menu_link', 'menu_icon', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
