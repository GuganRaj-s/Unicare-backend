<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HospitalSetting extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'hospital_detail_id', 'file_serial_number', 'prefix_text', 'prefix_number', 'start_prefix_after_number', 'insurance_detectable', 'email_required', 'referral_doctor_required', 'about_us_required', 'next_kin_required', 'allow_patient_register', 'hl_seven_registration', 'act_before_approval', 'lab_invest_no', 'radiology_invest_no', 'direct_patient_file_prefix', 'direct_patient_file_no', 'direct_patient_bill_prefix', 'direct_patient_bill_no', 'disable_auto_fill_doctor_order', 'appointment_color', 'allow_appolintment_other_doctor', 'change_attend_status_manually', 'center_day_off', 'malaffi_status', 'malaffi_inception_date', 'disable_chat_system', 'not_accept_investication', 'act_lab_radiology', 'service_date_bill_date', 'hide_date_emr', 'allow_consult_dept', 'hide_header_lab_report', 'claim_after_review', 'send_receipt_pharmacy', 'order_date_act_done_date', 'outsource_lab_credit', 'outsource_lab_cash', 'activate_emr_log', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
