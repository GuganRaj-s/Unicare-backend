<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeptCategory extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'description', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
