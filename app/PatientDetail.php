<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientDetail extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
         'hospital_detail_id', 'mr_number', 'register_no', 'register_time', 'register_date', 'user_created', 'patient_class_id', 'title_id', 'first_name', 'middle_name', 'last_name', 'full_name', 'arabic_name', 'date_of_birth', 'patient_age', 'member_type', 'gender_id', 'father_name', 'marital_status_id', 'religion_id', 'nationality_id', 'blood_group_id', 'ethnic_id', 'language_id', 'education_id', 'occupation_id', 'passport_no', 'primary_mobile', 'secondary_mobile', 'primary_email', 'industry_id', 'income_range_id', 'patient_status', 'deceased_date', 'payment_mode_id', 'profile_image', 'referral_source_id', 'medical_tourism', 'visitor_type', 'phc_status', 'phc_startdate', 'is_active', 'created_at', 'created_by', 'updated_at', 'updated_by'
    ];

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
