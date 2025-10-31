<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'short_code', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
