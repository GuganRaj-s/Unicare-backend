<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientAddress extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'patient_detail_id', 'address', 'country_id', 'region_id', 'city_id', 'religion_id', 'post_box_no', 'home_telephone', 'work_telephone', 'whatsup_number', 'primary_contact_code', 'secondary_contact_code', 'primary_contact', 'secondary_contact', 'email', 'referral_source_id', 'no_email_available', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
