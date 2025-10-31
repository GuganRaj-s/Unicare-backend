<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientInsurance extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'patient_detail_id', 'insurance_company_detail_id', 'sub_company_detail_id', 'insurance_package_id', 'insurance_network_id', 'insurance_plan_id', 'policy_holder', 'card_number', 'expiry_date', 'max_ceilling', 'deduct_amount', 'co_pay_amount', 'co_pay_all_service', 'is_status', 'company_type_id', 'with_consultation', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];

    protected $casts = [
        'expiry_date' => 'date:d-m-Y'
    ];

    public function detail()
    {
        return $this->hasMany('App\PatientInsuranceDetail', 'patient_detail_id', 'id');
    }
}
