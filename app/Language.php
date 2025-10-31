<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'short_code', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
