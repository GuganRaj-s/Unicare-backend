<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientKin extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'patient_detail_id', 'firstname', 'middlename', 'lastname', 'mr_number', 'mobile_no', 'email', 'address', 'relationship_id', 'country_id', 'region_id', 'city_id', 'post_box_no', 'landline_number', 'office_phone', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
