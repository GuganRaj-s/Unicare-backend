<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientEnquiry extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
         'first_name', 'last_name', 'middle_name', 'contact_no', 'doctor_id', 'staff_id', 'referral_doctor_id', 'department_id', 'enquiry_reason_id', 'appointment_date', 'comments', 'time_interval', 'to_time', 'from_time', 'is_active', 'created_at', 'created_by', 'updated_at', 'updated_by'
    ];

    protected $casts = [
        'appointment_date' => 'date:d-m-Y'
    ];
}
