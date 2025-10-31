<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlanDetail extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'hospital_detail_id','insurance_company_detail_id','insurance_network_id','insurance_plan_id','plan_require_approval','before_discount','after_discount','validity_approve_days','limit_per_invoice','discontinue_network','discontinue_plan','free_followup_days','max_ceiling','co_insurance_exist_patient','deduct_exist_patient','discount_all_network','discount_all_plan','factor_all_network','factor_all_plans','is_active','created_at', 'updated_at', 'created_by', 'updated_by'
    ];

    public function description()
    {
        return $this->hasMany('App\PlanDetailDescription', 'plan_detail_id', 'id');
    }
}
