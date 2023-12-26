<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'name', 'country_id', 'short_name', 'is_health_autority', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
