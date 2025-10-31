<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InsurancePackage extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'insurance_company_detail_id', 'payer_ids', 'name', 'product_name', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
