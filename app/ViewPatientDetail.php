<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewPatientDetail extends Model
{
    protected $connection = 'mysql';
    public $table = "view_patient_details";

    protected $casts = [
        'date_of_birth' => 'date:d-m-Y',
        'deceased_date' => 'date:d-m-Y',
        'expiry_date' => 'date:d-m-Y',
        'register_date' => 'date:d-m-Y',
        'payment_mode_id' => 'integer'
    ];

    public function emirate()
    {
        return $this->hasMany('App\PatientEmirate', 'patient_detail_id', 'id');
    }

    public function sub_address()
    {
        return $this->hasMany('App\PatientAddress', 'patient_detail_id', 'id');
    }

    public function others()
    {
         return $this->hasMany('App\PatientOther', 'patient_detail_id', 'id');
    }

    public function guardian()
    {
        return $this->hasMany('App\ViewPatientGuardian', 'patient_detail_id', 'id');
    }

    public function patientkin()
    {
        return $this->hasMany('App\ViewPatientKin', 'patient_detail_id', 'id');
    }
    
    public function referral()
    {
        return $this->hasMany('App\PatientReferral', 'patient_detail_id', 'id');
    }
    
}
