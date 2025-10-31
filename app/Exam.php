<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
