<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlockPatientAppointment extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'hospital_detail_id',
        'patient_detail_id',
        'doctor_id',
        'block_reason',
        'block_status',
        'patient_notes',
        'block_status_color',
        'user_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}