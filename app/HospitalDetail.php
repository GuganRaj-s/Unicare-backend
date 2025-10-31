<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HospitalDetail extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'arabic_name', 'address_line1', 'address_line2', 'city_id', 'email', 'region_id', 'country_id', 'webiste_url', 'phone_number', 'fax_number', 'location_url', 'short_name', 'small_logo', 'header_logo', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
