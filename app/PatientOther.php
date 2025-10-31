<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientOther extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'patient_detail_id', 'unified_no', 'mothers_eid', 'multiple_birth', 'birth_order', 'birth_place', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
