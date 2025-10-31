<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeptCategory extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'description', 'short_code', 'category_type', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
