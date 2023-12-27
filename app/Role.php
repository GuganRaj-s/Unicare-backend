<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'is_active','created_on'
    ];

}
