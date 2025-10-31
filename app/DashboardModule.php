<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DashboardModule extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'menu_order', 'menu_link', 'menu_icon', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
