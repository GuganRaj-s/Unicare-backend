<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthAuthority extends Model
{
    protected $fillable = [
        'facility_license_no', 'hospital_detail_id', 'region_id', 'username', 'password', 'web_service_url', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
