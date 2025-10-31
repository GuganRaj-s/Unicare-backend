<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InsuranceNetwork extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'insurance_company_detail_id', 'network_type', 'name', 'is_status', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];

}
