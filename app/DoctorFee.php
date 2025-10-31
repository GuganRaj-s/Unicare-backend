<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DoctorFee extends Model
{
    protected $connection = 'mysql';
    protected $table = 'doctor_fee';
    protected $fillable = [
        'doctor_id', 'consultation', 'charges', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
