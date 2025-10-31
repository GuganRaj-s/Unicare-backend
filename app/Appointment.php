<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'hospital_detail_id', 'enquiry_no', 'doctor_id', 'staff_id', 'patient_detail_id', 'exam_id', 'visit_type_id', 'appointment_date', 'appointment_time', 'time_interval', 'app_start', 'app_end', 'enquiry_service_id', 'enquiry_reason_id', 'notes', 'consent_forms', 'color_code', 'appointment_status_id', 'is_recurring', 'recurrence_rule', 'allow_overlapping', 'followup_reminder', 'followup_date', 'followup_reason', 'week_days', 'first_appointment_date' ,'last_appointment_date', 'never_ends', 'payment_mode', 'patient_insurance_id', 'time_end', 'time_start', 'phc_status', 'did_you_know', 'iswalkin', 'bookreminder','group_id','isDoubleBooking', 'idDoubleBookingExist', 'isBetweenSlot', 'checkIn', 'checkOut', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'appointment_date' => 'date:d-m-Y',
        'followup_date' => 'date:d-m-Y',
        'appointment_time' => 'time:h:i A',
        'first_appointment_date' => 'date:d-m-Y',
        'last_appointment_date' => 'date:d-m-Y',
    ];
}
