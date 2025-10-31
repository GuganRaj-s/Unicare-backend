<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralDoctor extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'id', 'name', 'license_no', 'referral_clinic_id', 'qualification', 'is_status', 'address', 'country_id', 'region_id', 'city_id', 'phone_no', 'mobile_no', 'email', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
