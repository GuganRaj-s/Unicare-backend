<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientInsuranceDetail extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'patient_detail_id', 'patient_insurance_id', 'department_id', 'co_pay_percentage', 'deductible_amount', 'per_invoice', 'max_ceilling', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
