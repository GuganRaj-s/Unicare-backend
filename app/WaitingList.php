<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WaitingList extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'firstname','middleName','lastName', 'hospital_detail_id', 'patient_type', 'patient_id', 'contact_number', 'patient_rating', 'others', 'doctor_id', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
