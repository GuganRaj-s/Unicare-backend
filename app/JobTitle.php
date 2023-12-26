<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model
{
    protected $fillable = [
        'name', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
