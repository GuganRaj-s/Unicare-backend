<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneEnquiry extends Model
{
    protected $connection = 'mysql';
    protected $table = 'phone_enquiries';
    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'name', 'hospital_detail_id', 'have_emirates_ids', 'primary_number', 'secondary_number', 'whatsup_number', 'doctor_id', 'patient_id', 'apponitment_id', 'staff_id', 'enquiry_service_id', 'department_id', 'enquiry_reason_id', 'appointment_date', 'comments', 'time_interval', 'from_time', 'to_time', 'app_start_time', 'app_end_time','group_id', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by', 'color_code','appointment_status_id','isDoubleBooking' ,'idDoubleBookingExist', 'isBetweenSlot', 'checkIn', 'checkOut'
    ];


    protected $casts = [
        'appointment_date' => 'date:d-m-Y'
    ];
}
