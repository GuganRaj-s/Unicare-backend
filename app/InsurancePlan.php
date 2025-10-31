<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InsurancePlan extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'insurance_company_detail_id',  'hospital_detail_id', 'insurance_network_id', 'plan_name', 'network_separate_price',  'is_status', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
