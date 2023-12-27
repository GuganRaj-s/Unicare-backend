<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'country_id', 'short_name', 'region_id', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
