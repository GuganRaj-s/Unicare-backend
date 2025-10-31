<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TypesAbuse extends Model
{
    protected $connection = 'mysql';
    protected $table = 'type_abuse';
    protected $fillable = [
        'name', 'short_code', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
