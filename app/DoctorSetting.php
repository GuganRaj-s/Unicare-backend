<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DoctorSetting extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'user_id', 'shift_master_id', 'slot_interval', 'view_appointment', 'license_no', 'expiry_date', 'notify_expiry_days', 'clinician_type', 'em_guidelines', 'em_validator', 'lock_encounter_days', 'maternity_chart', 'followUp_required_EMR', 'child_mental_health', 'disable_SMS_doctor', 'disable_exam_normal', 'copy_prescription', 'unsigned_charts', 'refresh_time_unsigned_charts', 'department_category_id', 'is_active', 'active', 'created_by', 'created_at', 'updated_by', 'updated_at'   
    ];
}
