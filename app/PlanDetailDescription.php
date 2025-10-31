<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlanDetailDescription extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'dept_category_id', 'department_id','plan_detail_id','out_patient','out_patient_discount','in_patient', 'in_patient_discount','co_ins_ongross','co_ins_onnet','co_pay_percentage', 'dedcut_amount', 'per_request', 'factor', 'sort_by', 'bill_exceeds', 'is_active','created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
