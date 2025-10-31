<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientReferral extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'patient_detail_id', 'doctor_name', 'clinic_name', 'license_no', 'referral_channel_id', 'referral_source_id', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
