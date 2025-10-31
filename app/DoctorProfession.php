<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DoctorProfession extends Model
{
    protected $connection = 'mysql';
    protected $table = 'doctor_profession';
    protected $fillable = [
        'name', 'short_code', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
