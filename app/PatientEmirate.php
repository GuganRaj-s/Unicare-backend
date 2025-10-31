<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientEmirate extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'patient_detail_id', 'emirate_ids', 'expiry_date', 'emirate_ids_front', 'emirate_ids_back', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];

    protected $casts = [
        'expiry_date' => 'date:d-m-Y',
    ];
}
