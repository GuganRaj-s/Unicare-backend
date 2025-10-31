<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\Appointment;
use App\BlockDoctorAppointment;
use App\BlockTempAppointment;
use App\BlockPatientAppointment;
use App\ViewBlockDoctorAppointment;
use App\ViewBlockTempAppointment;
use App\RecurringAppointment;
use App\ViewRecurringAppointment;
use App\ViewAppointment;
use App\ViewAppointmentDetail;
use App\ViewPhoneEnquiry;
use App\ViewBlockPatientAppointment;
use App\AppointmentLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use \Validator;
use \stdClass;
use DateTime;
use DateInterval;

class AppointmentController extends BaseController {
    public function BookAppointment(Request $request) {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'doctor_id' => 'required|integer|exists:users,id,role_id,4,is_active,1',
                'hospital_detail_id' => 'required|integer|exists:hospital_details,id,is_active,1',
                'patient_detail_id' => 'required|integer|exists:patient_details,id,is_active,1',
                'exam_id' => 'required|integer|exists:exams,id,is_active,1',
                'visit_type_id' => 'required|integer|exists:visit_types,id,is_active,1',
                'appointment_status_id' => 'required|integer|exists:appointment_statuses,id,is_active,1',
                'appointment_date' => 'required|date_format:Y-m-d',
                'appointment_time' => 'required|date_format:H:i:s',
                'time_interval' => 'required|integer|min:1|max:240',
                'enquiry_reason_id' => 'required|integer|exists:enquiry_reasons,id,is_active,1',
                'enquiry_service_id' => 'required|integer|exists:enquiry_services,id,is_active,1',
                'notes' => 'nullable|string',
                'color_code' => 'required|string|min:3|max:20',
                'recurrence_rule' => 'nullable|string|min:0|max:200',
                'allow_overlapping' => 'required|in:0,1',
                'payment_mode' => 'nullable|string|min:0|max:5',
                'patient_insurance_id' => 'nullable|integer|exists:patient_insurances,id,is_active,1',
                'phc_status' => 'nullable|integer|in:0,1',
                'did_you_know' => 'nullable|integer|in:0,1',
                'iswalkin' => 'nullable|string',
                'bookreminder' => 'nullable|string',
                'group_id' => 'nullable|string|min:0|max:100',
                'isDoubleBooking' => 'required|in:0,1',
                'idDoubleBookingExist' => 'required|in:0,1',
                'isBetweenSlot' => 'required|in:0,1',
                'checkIn' => 'nullable|date_format:H:i:s',
                'checkOut' => 'nullable|date_format:H:i:s'
            ]);
    
            if ($validator->fails()) {
                Log::debug('API BookAppointment :: Validation failed', ['errors' => $validator->errors()]);
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 2) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            // Format appointment date and time
            $appointment_date = $request->appointment_date;
            $appointment_time = $request->appointment_time;
            $app_start = "$appointment_date $appointment_time";
            $time_interval = (int)$request->time_interval;
    
            // Validate date-time parsing
            try {
                $start_dt = new DateTime($app_start);
                $end_dt = (clone $start_dt)->modify("+{$time_interval} minutes");
            } catch (\Exception $e) {
                Log::debug('API BookAppointment :: Invalid date/time format: ' . $e->getMessage());
                return $this->sendResponse(0, 200, 'Invalid appointment date or time format.');
            }
    
            if ($end_dt <= $start_dt) {
                return $this->sendResponse(0, 200, 'Appointment end time must be after start time.');
            }
    
            $app_end = $end_dt->format('Y-m-d H:i:s');
            $time_start = $start_dt->format('YmdHis');
            $time_end = $end_dt->format('YmdHis');
    
            // Debug: Log time values
            Log::debug('API BookAppointment :: Checking appointment', [
                'app_start' => $app_start,
                'app_end' => $app_end,
                'time_start' => $time_start,
                'time_end' => $time_end,
                'doctor_id' => $request->doctor_id,
                'hospital_detail_id' => $request->hospital_detail_id,
                'time_interval' => $time_interval,
                'isDoubleBooking' => $request->isDoubleBooking,
                'idDoubleBookingExist' => $request->idDoubleBookingExist,
                'isBetweenSlot' => $request->isBetweenSlot
            ]);
    
            // Check conditions based on isDoubleBooking, idDoubleBookingExist, and isBetweenSlot
            if ($request->isDoubleBooking == 0 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 0) {
                // Case 1: Check all conditions
                if ($request->allow_overlapping == 0) {
                    $overlapCount = DB::select("
                        SELECT COUNT(*) as block_count
                        FROM appointments
                        WHERE doctor_id = ?
                        AND hospital_detail_id = ?
                        AND appointment_status_id != 12
                        AND is_active = 1
                        AND time_start < ?
                        AND time_end > ?
                    ", [$request->doctor_id, $request->hospital_detail_id, $time_end, $time_start])[0]->block_count;
    
                    // Debug: Log overlap check result and existing appointments
                    $existingAppointments = DB::select("
                        SELECT id, time_start, time_end, app_start, app_end
                        FROM appointments
                        WHERE doctor_id = ?
                        AND hospital_detail_id = ?
                        AND appointment_status_id != 12
                        AND is_active = 1
                        AND appointment_date = ?
                    ", [$request->doctor_id, $request->hospital_detail_id, $appointment_date]);
    
                    Log::debug('API BookAppointment :: Overlap check result', [
                        'overlap_count' => $overlapCount,
                        'query_conditions' => [
                            'doctor_id' => $request->doctor_id,
                            'hospital_detail_id' => $request->hospital_detail_id,
                            'time_start_lt' => $time_end,
                            'time_end_gt' => $time_start,
                            'appointment_date' => $appointment_date
                        ],
                        'existing_appointments' => $existingAppointments
                    ]);
    
                    if ($overlapCount > 0) {
                        return $this->sendResponse(0, 200, 'This slot already booked for another patient');
                    }
    
                    // Check blocked doctor slots
                    $block_date = $appointment_date;
                    $block_time_1 = $start_dt->format('His');
                    $block_time_2 = $end_dt->format('His');
    
                    $block_doctor = DB::select("
                        SELECT COUNT(*) as block_count
                        FROM view_block_doctor_appointments
                        WHERE doctor_id = ? 
                        AND hospital_detail_id = ? 
                        AND start_date <= ? 
                        AND end_date >= ? 
                        AND start_timestamp < ? 
                        AND end_timestamp > ?
                    ", [$request->doctor_id, $request->hospital_detail_id, $block_date, $block_date, $block_time_2, $block_time_1])[0]->block_count;
    
                    if ($block_doctor > 0) {
                        return $this->sendResponse(0, 200, 'This slot already blocked by doctor');
                    }
    
                    // Check temporary blocked slots
                    $block_temp = DB::select("
                        SELECT COUNT(*) as block_count
                        FROM view_block_temp_appointments
                        WHERE doctor_id = ? 
                        AND hospital_detail_id = ? 
                        AND start_date <= ? 
                        AND end_date >= ? 
                        AND start_timestamp < ? 
                        AND end_timestamp > ?
                    ", [$request->doctor_id, $request->hospital_detail_id, $block_date, $block_date, $block_time_2, $block_time_1])[0]->block_count;
    
                    if ($block_temp > 0) {
                        return $this->sendResponse(0, 200, 'This slot temporarily blocked by doctor');
                    }
    
                    // Check phone enquiry slots
                    $block_phone = DB::select("
                        SELECT COUNT(*) as block_count
                        FROM view_phone_enquiry
                        WHERE doctor_id = ? 
                        AND hospital_detail_id = ? 
                        AND appointment_date = ? 
                        AND app_start_time < ? 
                        AND app_end_time > ?
                    ", [$request->doctor_id, $request->hospital_detail_id, $block_date, $block_time_2, $block_time_1])[0]->block_count;
    
                    if ($block_phone > 0) {
                        return $this->sendResponse(0, 200, 'This slot already booked for another patient via phone enquiry');
                    }
                }
    
                // Check for previous/next day bookings
                $prevDay = date('Y-m-d', strtotime($appointment_date . ' -1 day'));
                $nextDay = date('Y-m-d', strtotime($appointment_date . ' +1 day'));
    
                $hasPreviousDayBooking = Appointment::where('patient_detail_id', $request->patient_detail_id)
                    ->where('doctor_id', $request->doctor_id)
                    ->where('appointment_time', $appointment_time)
                    ->whereDate('appointment_date', $prevDay)
                    ->where('appointment_status_id', '!=', 3)
                    ->where('is_active', 1)
                    ->exists();
    
                if ($hasPreviousDayBooking) {
                    return $this->sendResponse(0, 200, 'The patient already has an appointment with this doctor at the same time yesterday. Please reschedule.');
                }
    
                $hasNextDayBooking = Appointment::where('patient_detail_id', $request->patient_detail_id)
                    ->where('doctor_id', $request->doctor_id)
                    ->where('appointment_time', $appointment_time)
                    ->whereDate('appointment_date', $nextDay)
                    ->where('appointment_status_id', '!=', 3)
                    ->where('is_active', 1)
                    ->exists();
    
                if ($hasNextDayBooking) {
                    return $this->sendResponse(0, 200, 'The patient already has an appointment with this doctor at the same time tomorrow. Please reschedule.');
                }
            } else if ($request->isDoubleBooking == 1 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 0) {
                // Case 2: Check if slot is booked when isDoubleBooking is 1, and prevent booking if any existing appointment exists
                $overlapCount = DB::select("
                    SELECT COUNT(*) as block_count
                    FROM appointments
                    WHERE doctor_id = ?
                    AND hospital_detail_id = ?
                    AND is_active = 1
                    AND isDoubleBooking = 1
                    AND appointment_status_id != 12
                    AND time_start < ?
                    AND time_end > ?
                ", [$request->doctor_id, $request->hospital_detail_id, $time_end, $time_start])[0]->block_count;
    
                if ($overlapCount > 0) {
                    return $this->sendResponse(0, 200, 'This slot already booked for another patient');
                }
            } elseif ($request->isDoubleBooking == 1 && $request->idDoubleBookingExist == 1 && $request->isBetweenSlot == 0) {
                // Case 3: No need to check any conditions
                // Proceed to create appointment
            } elseif ($request->isDoubleBooking == 0 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 1) {
                // Case 4: No need to check any conditions
                // Proceed to create appointment
            }
    
            // Create appointment
            $patient = Appointment::create([
                'hospital_detail_id' => $request->hospital_detail_id,
                'doctor_id' => $request->doctor_id,
                'patient_detail_id' => $request->patient_detail_id,
                'exam_id' => $request->exam_id,
                'visit_type_id' => $request->visit_type_id,
                'appointment_date' => $appointment_date,
                'appointment_time' => $appointment_time,
                'time_interval' => $time_interval,
                'app_start' => $app_start,
                'app_end' => $app_end,
                'enquiry_service_id' => $request->enquiry_service_id,
                'enquiry_reason_id' => $request->enquiry_reason_id,
                'notes' => $request->notes,
                'color_code' => $request->color_code,
                'allow_overlapping' => $request->allow_overlapping,
                'appointment_status_id' => $request->appointment_status_id,
                'recurrence_rule' => $request->recurrence_rule,
                'payment_mode' => $request->payment_mode,
                'patient_insurance_id' => $request->patient_insurance_id,
                'phc_status' => $request->phc_status,
                'did_you_know' => $request->did_you_know,
                'iswalkin' => $request->iswalkin,
                'bookreminder' => $request->bookreminder,
                'group_id' => $request->group_id,
                'isDoubleBooking' => $request->isDoubleBooking,
                'idDoubleBookingExist' => $request->idDoubleBookingExist,
                'isBetweenSlot' => $request->isBetweenSlot,
                'checkIn' => $request->checkIn,
                'checkOut' => $request->checkOut,
                'time_start' => $time_start,
                'time_end' => $time_end,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
    
            if ($patient->id) {
                return $this->sendResponse(1, 200, 'Appointment booked successfully');
            } else {
                return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.');
            }
    
        } catch (\Exception $e) {
            Log::debug('API BookAppointment :: ' . $e->getMessage(), [
                'request' => $request->all(),
                'error' => $e->getTraceAsString()
            ]);
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function UpdateAppointment(Request $request) {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'appointment_id' => 'required|integer|exists:appointments,id,is_active,1',
                'doctor_id' => 'required|integer|exists:users,id,role_id,4,is_active,1',
                'staff_id' => 'nullable|integer|exists:users,id,role_id,5,is_active,1',
                'hospital_detail_id' => 'required|integer|exists:hospital_details,id,is_active,1',
                'patient_detail_id' => 'required|integer|exists:patient_details,id,is_active,1',
                'exam_id' => 'required|integer|exists:exams,id,is_active,1',
                'visit_type_id' => 'required|integer|exists:visit_types,id,is_active,1',
                'appointment_status_id' => 'required|integer|exists:appointment_statuses,id,is_active,1',
                'appointment_date' => 'required|date_format:Y-m-d',
                'appointment_time' => 'required|date_format:H:i:s',
                'time_interval' => 'required|integer|min:1|max:240',
                'enquiry_reason_id' => 'required|integer|exists:enquiry_reasons,id,is_active,1',
                'enquiry_service_id' => 'required|integer|exists:enquiry_services,id,is_active,1',
                'notes' => 'nullable|string',
                'consent_forms' => 'nullable',
                'color_code' => 'required|string|min:6|max:20',
                'recurrence_rule' => 'nullable|string|min:0|max:200',
                'allow_overlapping' => 'required|in:0,1',
                'payment_mode' => 'nullable|string|min:0|max:5',
                'patient_insurance_id' => 'nullable|integer|exists:patient_insurances,id,is_active,1',
                'group_id' => 'nullable|string|min:0|max:100',
                'isDoubleBooking' => 'required|in:0,1',
                'idDoubleBookingExist' => 'required|in:0,1',
                'isBetweenSlot' => 'required|in:0,1',
                'checkIn' => 'nullable|date_format:H:i:s',
                'checkOut' => 'nullable|date_format:H:i:s'
            ]);
    
            if ($validator->fails()) {
                Log::debug('API UpdateAppointment :: Validation failed', ['errors' => $validator->errors()]);
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 2) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            $app_id = $request->appointment_id;
            $exist_app = Appointment::where(['id' => $app_id, 'is_active' => 1])->first();
            if (!$exist_app) {
                return $this->sendResponse(0, 200, 'Record not found. Try again.');
            }
    
            // Format appointment date and time
            $appointment_date = $request->appointment_date;
            $appointment_time = $request->appointment_time;
            $app_start = "$appointment_date $appointment_time";
            $time_interval = (int)$request->time_interval;
    
            // Validate date-time parsing
            try {
                $start_dt = new DateTime($app_start);
                $end_dt = (clone $start_dt)->modify("+{$time_interval} minutes");
            } catch (\Exception $e) {
                Log::debug('API UpdateAppointment :: Invalid date/time format: ' . $e->getMessage());
                return $this->sendResponse(0, 200, 'Invalid appointment date or time format.');
            }
    
            if ($end_dt <= $start_dt) {
                return $this->sendResponse(0, 200, 'Appointment end time must be after start time.');
            }
    
            $app_end = $end_dt->format('Y-m-d H:i:s');
            $time_start = $start_dt->format('YmdHis');
            $time_end = $end_dt->format('YmdHis');
    
            // Debug: Log time values
            Log::debug('API UpdateAppointment :: Checking appointment', [
                'appointment_id' => $app_id,
                'app_start' => $app_start,
                'app_end' => $app_end,
                'time_start' => $time_start,
                'time_end' => $time_end,
                'doctor_id' => $request->doctor_id,
                'hospital_detail_id' => $request->hospital_detail_id,
                'time_interval' => $time_interval,
                'isDoubleBooking' => $request->isDoubleBooking,
                'idDoubleBookingExist' => $request->idDoubleBookingExist,
                'isBetweenSlot' => $request->isBetweenSlot
            ]);
    
            // Check conditions based on isDoubleBooking, idDoubleBookingExist, and isBetweenSlot
            if ($request->isDoubleBooking == 0 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 0) {
                // Case 1: Check all conditions
                if ($request->allow_overlapping == 0) {
                    $overlapCount = DB::select("
                        SELECT COUNT(*) as block_count
                        FROM appointments
                        WHERE id != ?
                        AND doctor_id = ?
                        AND hospital_detail_id = ?
                        AND is_active = 1
                        AND appointment_date = ?
                        AND time_start < ?
                        AND time_end > ?
                    ", [$app_id, $request->doctor_id, $request->hospital_detail_id, $appointment_date, $time_end, $time_start])[0]->block_count;
    
                    // Debug: Log overlap check result and existing appointments
                    $existingAppointments = DB::select("
                        SELECT id, time_start, time_end, app_start, app_end
                        FROM appointments
                        WHERE doctor_id = ?
                        AND hospital_detail_id = ?
                        AND is_active = 1
                        AND appointment_date = ?
                        AND id != ?
                    ", [$request->doctor_id, $request->hospital_detail_id, $appointment_date, $app_id]);
    
                    Log::debug('API UpdateAppointment :: Overlap check result', [
                        'overlap_count' => $overlapCount,
                        'query_conditions' => [
                            'appointment_id' => $app_id,
                            'doctor_id' => $request->doctor_id,
                            'hospital_detail_id' => $request->hospital_detail_id,
                            'time_start_lt' => $time_end,
                            'time_end_gt' => $time_start,
                            'appointment_date' => $appointment_date
                        ],
                        'existing_appointments' => $existingAppointments
                    ]);
    
                    if ($overlapCount > 0) {
                        return $this->sendResponse(0, 200, 'This slot already booked for another patient');
                    }
    
                    // Check blocked doctor slots
                    $block_date = $appointment_date;
                    $block_time_1 = $start_dt->format('His');
                    $block_time_2 = $end_dt->format('His');
    
                    $block_doctor = DB::select("
                        SELECT COUNT(*) as block_count
                        FROM view_block_doctor_appointments
                        WHERE doctor_id = ? 
                        AND hospital_detail_id = ? 
                        AND start_date <= ? 
                        AND end_date >= ? 
                        AND start_timestamp < ? 
                        AND end_timestamp > ?
                    ", [$request->doctor_id, $request->hospital_detail_id, $block_date, $block_date, $block_time_2, $block_time_1])[0]->block_count;
    
                    if ($block_doctor > 0) {
                        return $this->sendResponse(0, 200, 'This slot already blocked by doctor');
                    }
    
                    // Check temporary blocked slots
                    $block_temp = DB::select("
                        SELECT COUNT(*) as block_count
                        FROM view_block_temp_appointments
                        WHERE doctor_id = ? 
                        AND hospital_detail_id = ? 
                        AND start_date <= ? 
                        AND end_date >= ? 
                        AND start_timestamp < ? 
                        AND end_timestamp > ?
                    ", [$request->doctor_id, $request->hospital_detail_id, $block_date, $block_date, $block_time_2, $block_time_1])[0]->block_count;
    
                    if ($block_temp > 0) {
                        return $this->sendResponse(0, 200, 'This slot temporarily blocked by doctor');
                    }
    
                    // Check phone enquiry slots
                    $block_phone = DB::select("
                        SELECT COUNT(*) as block_count
                        FROM view_phone_enquiry
                        WHERE doctor_id = ? 
                        AND hospital_detail_id = ? 
                        AND appointment_date = ? 
                        AND app_start_time < ? 
                        AND app_end_time > ?
                    ", [$request->doctor_id, $request->hospital_detail_id, $block_date, $block_time_2, $block_time_1])[0]->block_count;
    
                    if ($block_phone > 0) {
                        return $this->sendResponse(0, 200, 'This slot already booked for another patient via phone enquiry');
                    }
                }
    
                // Check for previous/next day bookings
                $prevDay = date('Y-m-d', strtotime($appointment_date . ' -1 day'));
                $nextDay = date('Y-m-d', strtotime($appointment_date . ' +1 day'));
    
                $hasPreviousDayBooking = Appointment::where('patient_detail_id', $request->patient_detail_id)
                    ->where('doctor_id', $request->doctor_id)
                    ->where('appointment_time', $appointment_time)
                    ->whereDate('appointment_date', $prevDay)
                    ->where('appointment_status_id', '!=', 3)
                    ->where('id', '!=', $app_id)
                    ->where('is_active', 1)
                    ->exists();
    
                if ($hasPreviousDayBooking) {
                    return $this->sendResponse(0, 200, 'The patient already has an appointment with this doctor at the same time yesterday. Please reschedule.');
                }
    
                $hasNextDayBooking = Appointment::where('patient_detail_id', $request->patient_detail_id)
                    ->where('doctor_id', $request->doctor_id)
                    ->where('appointment_time', $appointment_time)
                    ->whereDate('appointment_date', $nextDay)
                    ->where('appointment_status_id', '!=', 3)
                    ->where('id', '!=', $app_id)
                    ->where('is_active', 1)
                    ->exists();
    
                if ($hasNextDayBooking) {
                    return $this->sendResponse(0, 200, 'The patient already has an appointment with this doctor at the same time tomorrow. Please reschedule.');
                }
            } elseif ($request->isDoubleBooking == 1 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 0) {
                // Case 2: Check if slot is booked when isDoubleBooking is 1, but dont allow if existing appointment has isDoubleBooking = 1
                
                $overlapCount = DB::select("
                    SELECT COUNT(*) as block_count
                    FROM appointments
                    WHERE id != ?
                    AND doctor_id = ?
                    AND hospital_detail_id = ?
                    AND isDoubleBooking = 1
                    AND is_active = 1
                    AND time_start < ?
                    AND time_end > ?
                ", [$request->doctor_id, $request->hospital_detail_id, $time_end, $time_start])[0]->block_count;
    
                if ($overlapCount > 0) {
                    return $this->sendResponse(0, 200, 'This slot already booked for another patient');
                }
            } elseif ($request->isDoubleBooking == 1 && $request->idDoubleBookingExist == 1 && $request->isBetweenSlot == 0) {
                // Case 3: No need to check any conditions
                // Proceed to update appointment
            } elseif ($request->isDoubleBooking == 0 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 1) {
                // Case 4: No need to check any conditions
                // Proceed to update appointment
            }
    
            // Update appointment
            $appointment = Appointment::find($app_id);
            $appointment->hospital_detail_id = $request->hospital_detail_id;
            $appointment->doctor_id = $request->doctor_id;
            $appointment->staff_id = $request->staff_id;
            $appointment->patient_detail_id = $request->patient_detail_id;
            $appointment->exam_id = $request->exam_id;
            $appointment->visit_type_id = $request->visit_type_id;
            $appointment->appointment_date = $appointment_date;
            $appointment->appointment_time = $appointment_time;
            $appointment->time_interval = $time_interval;
            $appointment->app_start = $app_start;
            $appointment->app_end = $app_end;
            $appointment->enquiry_service_id = $request->enquiry_service_id;
            $appointment->enquiry_reason_id = $request->enquiry_reason_id;
            $appointment->notes = $request->notes;
            $appointment->color_code = $request->color_code;
            $appointment->appointment_status_id = $request->appointment_status_id;
            $appointment->recurrence_rule = $request->recurrence_rule;
            $appointment->payment_mode = $request->payment_mode;
            $appointment->patient_insurance_id = $request->patient_insurance_id;
            $appointment->group_id = $request->group_id;
            $appointment->isDoubleBooking = $request->isDoubleBooking;
            $appointment->idDoubleBookingExist = $request->idDoubleBookingExist;
            $appointment->isBetweenSlot = $request->isBetweenSlot;
            $appointment->checkIn = $request->checkIn;
            $appointment->checkOut = $request->checkOut;
            $appointment->time_start = $time_start;
            $appointment->time_end = $time_end;
            $appointment->updated_by = $request->user_id;
            $appointment->updated_at = now();
            $appointment->save();
    
            if ($appointment->id) {
                $this->UpdateLogs($request->user_id, $app_id, 'AppointmentLog', 'Appointment', $exist_app, $appointment);
                return $this->sendResponse(1, 200, 'Appointment updated successfully');
            } else {
                return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.');
            }
    
        } catch (\Exception $e) {
            Log::debug('API UpdateAppointment :: ' . $e->getMessage(), [
                'request' => $request->all(),
                'error' => $e->getTraceAsString()
            ]);
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function ViewAppointment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|array',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1',
                'start_date'        => 'required|date',
                'end_date'          => 'required|date'
            ]);
    
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 2) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            $start_date = '';
            if ($request->start_date != '') {
                $start_date = date("Y-m-d", strtotime($request->start_date)); 
            } else {
                $start_date = date('Y-m-d');
            }
    
            $end_date = ''; 
            if ($request->end_date != '') {
                $end_date = date("Y-m-d", strtotime($request->end_date)); 
            } else {
                $end_date = date('Y-m-d');
            }
    
            $condition = '';
            if ($request->hospital_detail_id != '') {
                $hospital_detail_id = $request->hospital_detail_id;
                $condition .= " AND hospital_detail_id = $hospital_detail_id ";
            }
            
            if ($request->doctor_id != '') {
                $doctor_id = $request->doctor_id;
                $doctor_ids = implode(',', $doctor_id);
                $condition .= "AND doctor_id IN ($doctor_ids) ";
            }
    
            $image_path = config('app.image_path');
            $path = $image_path . 'patient/';
    
            $patient = DB::select("SELECT id, doctor_name, doctor_id, patient_detail_id, staff_name, hospital_detail_id, appointment_date, appointment_time, time_interval, app_reason, app_service, notes, color_code, exam_name, patient_name, mr_number, date_of_birth, patient_age, nationality_name, city_name, region_name, CONCAT('http://api.unicaredev.in/patient/', profile_image) as profile_image, location_name, visit_type, recurrence_rule, status_name, booked_by, payment_mode, insurance_company_name, insurance_company_shortcode, primary_contact_code, primary_contact, date_time, allow_overlapping, phc_status, did_you_know, iswalkin, bookreminder, group_id, isDoubleBooking, idDoubleBookingExist, isBetweenSlot, checkIn, checkOut FROM view_appointment_details WHERE appointment_date >= '$start_date' AND appointment_date <= '$end_date' $condition ORDER BY id DESC");
    
            $phone_enquiry = DB::select("SELECT id, patient_name, primary_number, secondary_number, whatsup_number, have_emirates_ids, doctor_name, staff_name, doctor_id, patient_id, service_name, department_name, enquiry_reason, appointment_date, comments, time_interval, from_time, to_time, booked_by, status_name, color_code, group_id, isDoubleBooking, idDoubleBookingExist, isBetweenSlot, checkIn, checkOut FROM view_phone_enquiry WHERE appointment_date >= '$start_date' AND appointment_date <= '$end_date' $condition ORDER BY id DESC");
    
            $temp_block = DB::select("SELECT id, hospital_detail_id, doctor_id, start_date, end_date, start_time, end_time, is_recursive, doctor_name, location_name, remarks FROM view_block_temp_appointments WHERE start_date >= '$start_date' AND end_date <= '$end_date' AND hospital_detail_id = $hospital_detail_id AND doctor_id IN($doctor_ids)");
    
            $doctor_block = DB::select("SELECT id, hospital_detail_id, doctor_id, start_date, end_date, start_time, end_time, is_recursive, doctor_name, location_name, remarks FROM view_block_doctor_appointments WHERE start_date >= '$start_date' AND end_date <= '$end_date' AND hospital_detail_id = $hospital_detail_id AND doctor_id IN($doctor_ids)");
    
            // Check if any appointment has isDoubleBooking = 1
            $hasDoubleBooking = false;
            foreach ($patient as $appointment) {
                if ($appointment->isDoubleBooking == 1) {
                    $hasDoubleBooking = true;
                    break;
                }
            }
            foreach ($phone_enquiry as $phone_appointment) {
                if ($phone_appointment->isDoubleBooking == 1) {
                    $hasDoubleBooking = true;
                    break;
                }
            }
    
            $response = [];
            $response['isDoubleBooking'] = $hasDoubleBooking;
            $response['patient'] = $patient;
            $response['phone_enquiry'] = $phone_enquiry;
            $response['doctor_block'] = $doctor_block;
            $response['temp_block'] = $temp_block;
    
            return $this->sendResponse(1, 200, 'Success', 'data', $response);
    
        } catch (\Exception $e) {
            Log::debug('API ViewAppointment :: ' . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function CheckAppointmentExistence(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|integer|exists:users,id,role_id,4',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1',
                'appointment_date'  => 'required|date',
                'start_time'        => 'required|date_format:H:i:s',
                'end_time'          => 'required|date_format:H:i:s|after:start_time'
            ]);
    
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 2) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            $appointment_date = date("Y-m-d", strtotime($request->appointment_date));
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $hospital_detail_id = $request->hospital_detail_id;
            $doctor_id = $request->doctor_id;
    
            $condition = " AND hospital_detail_id = $hospital_detail_id AND doctor_id = $doctor_id ";
    
            $patient_exists = DB::select("SELECT 1 FROM view_appointment_details 
                WHERE appointment_date = ? 
                AND appointment_time >= ? 
                AND appointment_time <= ? $condition 
                LIMIT 1", [$appointment_date, $start_time, $end_time]);
    
            $phone_enquiry_exists = DB::select("SELECT 1 FROM view_phone_enquiry 
                WHERE appointment_date = ? 
                AND from_time >= ? 
                AND to_time <= ? $condition 
                LIMIT 1", [$appointment_date, $start_time, $end_time]);
    
            $temp_block_exists = DB::select("SELECT 1 FROM view_block_temp_appointments 
                WHERE start_date = ? 
                AND start_time >= ? 
                AND end_time <= ? 
                AND hospital_detail_id = ? 
                AND doctor_id = ? 
                LIMIT 1", [$appointment_date, $start_time, $end_time, $hospital_detail_id, $doctor_id]);
    
            $doctor_block_exists = DB::select("SELECT 1 FROM view_block_doctor_appointments 
                WHERE start_date = ? 
                AND start_time >= ? 
                AND end_time <= ? 
                AND hospital_detail_id = ? 
                AND doctor_id = ? 
                LIMIT 1", [$appointment_date, $start_time, $end_time, $hospital_detail_id, $doctor_id]);
    
            $result = [
                'patient_appointment_exists' => !empty($patient_exists),
                'phone_enquiry_exists' => !empty($phone_enquiry_exists),
                'temp_block_exists' => !empty($temp_block_exists),
                'doctor_block_exists' => !empty($doctor_block_exists),
                'any_exists' => !empty($patient_exists) || !empty($phone_enquiry_exists) || 
                              !empty($temp_block_exists) || !empty($doctor_block_exists)
            ];
    
            return $this->sendResponse(1, 200, 'Success', 'data', $result);
    
        } catch (\Exception $e) {
            Log::debug('API CheckAppointmentExistence :: ' . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function ViewPatientAppointment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'patient_detail_id' => 'required|integer|exists:patient_details,id,is_active,1',
                'hospital_detail_id' => 'required|integer|exists:hospital_details,id,is_active,1'
            ]);
    
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 2) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            $patient_detail_id = $request->patient_detail_id;
            $hospital_detail_id = $request->hospital_detail_id;
    
            $image_path = config('app.image_path');
            $path = $image_path . 'patient/';
    
            $patient = DB::select("SELECT id, doctor_name, doctor_id, patient_detail_id, staff_name, hospital_detail_id, appointment_date, appointment_time, time_interval, app_reason, app_service, notes, color_code, exam_name, patient_name, mr_number, date_of_birth, patient_age, nationality_name, city_name, region_name, CONCAT('http://api.unicaredev.in/patient/', profile_image) as profile_image, location_name, visit_type, recurrence_rule, status_name, booked_by, payment_mode, insurance_company_name, insurance_company_shortcode, primary_contact_code, primary_contact, date_time, allow_overlapping, phc_status, did_you_know, iswalkin, bookreminder, group_id, isDoubleBooking, idDoubleBookingExist, isBetweenSlot, checkIn, checkOut FROM view_appointment_details WHERE patient_detail_id = ? AND hospital_detail_id = ? ORDER BY id DESC", [$patient_detail_id, $hospital_detail_id]);
    
            $phone_enquiry = DB::select("SELECT id, patient_name, primary_number, secondary_number, whatsup_number, have_emirates_ids, doctor_name, staff_name, doctor_id, patient_id, service_name, department_name, enquiry_reason, appointment_date, comments, time_interval, from_time, to_time, booked_by, status_name, color_code, group_id, isDoubleBooking, idDoubleBookingExist, isBetweenSlot, checkIn, checkOut FROM view_phone_enquiry WHERE patient_id = ? AND hospital_detail_id = ? ORDER BY id DESC", [$patient_detail_id, $hospital_detail_id]);
    
            // Check if any appointment has isDoubleBooking = 1
            $hasDoubleBooking = false;
            foreach ($patient as $appointment) {
                if ($appointment->isDoubleBooking == 1) {
                    $hasDoubleBooking = true;
                    break;
                }
            }
            foreach ($phone_enquiry as $phone_appointment) {
                if ($phone_appointment->isDoubleBooking == 1) {
                    $hasDoubleBooking = true;
                    break;
                }
            }
    
            $response = [];
            $response['isDoubleBooking'] = $hasDoubleBooking;
            $response['patient'] = $patient;
            $response['phone_enquiry'] = $phone_enquiry;
    
            return $this->sendResponse(1, 200, 'Success', 'data', $response);
    
        } catch (\Exception $e) {
            Log::debug('API ViewPatientAppointment :: ' . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleAppointment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'appointment_id'    => 'required|integer',

            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 2) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $appointment_id = $request->appointment_id;

            // $appointment =  ViewAppointment::SELECT('id','hospital_detail_id', 'doctor_id', 'staff_id', 'patient_detail_id', 'exam_id', 'visit_type_id', 'appointment_date', 'appointment_time', 'time_interval', 'enquiry_service_id', 'enquiry_reason_id', 'notes', 'color_code', 'appointment_status_id', 'allow_overlapping', 'recurrence_rule', 'first_name', 'middle_name', 'last_name', 'patient_insurance_id', 'payment_mode')
            $appointment =  ViewAppointmentDetail::SELECT('id', 'doctor_id', 'patient_detail_id', 'exam_id', 'hospital_detail_id', 'visit_type_id', 'appointment_date', 'appointment_time', 'time_interval', 'enquiry_reason_id', 'enquiry_service_id', 'notes', 'color_code', 'appointment_status_id', 'recurrence_rule', 'allow_overlapping', 'staff_name', 'exam_name', 'patient_name', 'register_no', 'mr_number', 'date_of_birth', 'patient_age', 'phc_status', 'did_you_know', 'iswalkin', 'bookreminder', 'group_id', 'nationality_name', 'city_name', 'region_name', 'profile_image', 'location_name', 'doctor_name', 'visit_type', 'app_service', 'app_reason', 'status_name', 'booked_by', 'payment_mode', 'patient_insurance_id', 'primary_contact_code', 'primary_contact', 'insurance_company_name', 'insurance_company_shortcode', 'date_time')
            ->WHERE(['id' => $appointment_id])->get();

            return $this->sendResponse(1,200, 'Success', 'data', $appointment);


        } catch(\Exception $e) {
            Log::debug('API GetSingleAppointment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateAppointmentStatus(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'appointment_id'    => 'required|integer',
                'color_code'        => 'required|string|min:6|max:20',
                'notes'             => 'nullable|string',
                'appointment_status_id'     => 'required|integer|exists:appointment_statuses,id,is_active,1',
                'checkOut' => 'nullable|date_format:H:i:s'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 2) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }
           
            $app_id = $request->appointment_id;
            $exist_app = Appointment::where(['id'=>$app_id, 'is_active' => 1])->get();
            if($exist_app->count() == 0) {
                return $this->sendResponse(0, 200, 'Record not found. Try again.');
            } 
            
            $appointment = Appointment::find($app_id);
            $appointment->appointment_status_id = $request->appointment_status_id;
            $appointment->color_code = $request->color_code;
            //$appointment->notes     = $request->notes;
            $appointment->checkOut = $request->checkOut;
            if(isset($request->notes) && $request->notes != ''){
                $appointment->notes   = $request->notes;
            }
            $appointment->updated_by = $request->user_id;
            $appointment->updated_at = date('Y-m-d H:i:s');
            $appointment->update();

            if($appointment->id) {
                $field_names = [
                    'appointment_status_id' => 'Appointment status changed',
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $app_id, 'AppointmentLog', 'Appointment', $exist_app, $appointment, $field_names);
                return $this->sendResponse(1,200, 'Appointment status updated successfully');
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateAppointmentStatus :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function ViewBlockAppointment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|integer|exists:users,id,role_id,4',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1',
                'start_date'        => 'required|date',
                'end_date'          => 'required|date'

            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 2) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $doctor_id = $request->doctor_id;
            $hospital_detail_id = $request->hospital_detail_id;
            $start_date = date("Y-m-d", strtotime($request->start_date)); 
            $end_date   = date("Y-m-d", strtotime($request->end_date)); 
            $transaction =  DB::select("SELECT id, hospital_detail_id, doctor_id, start_date,end_date, start_time, end_time, is_recursive, doctor_name, location_name, remarks  FROM view_block_doctor_appointments WHERE start_date >= '$start_date' AND end_date <= '$end_date' AND hospital_detail_id = $hospital_detail_id AND doctor_id = $doctor_id ");
    
            if(!empty($transaction)) {
                return $this->sendResponse(1,200, 'Success', 'data', $transaction);
            } else {
                return $this->sendResponse(0,200, 'Record not found');
            }
           
        } catch(\Exception $e) {
            Log::debug('API ViewBlockAppointment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function AddBlockAppointment(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|integer|exists:users,id,role_id,4',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 2) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            // if($this->VerifyPageAccess('patient', 'is_add') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }

            $appointments = $request->appointment;
            if(empty($appointments)) {
                return $this->sendResponse(0,200, 'Appointment detail is required');
            }

            foreach($appointments as $appt) {
                $start_date = null;
                $end_date = null;
                $start_time = null;
                $end_time = null;
                $remarks = $appt['remarks'];
                $is_recursive = $appt['is_recursive'];
                if($appt['start_date'] != ''){
                    $start_date = date("Y-m-d", strtotime($appt['start_date'])); 
                }
                if($appt['end_date'] != ''){
                    $end_date = date("Y-m-d", strtotime($appt['end_date'])); 
                }
                if($appt['start_time'] != ''){
                    $start_time = date("H:i:s", strtotime($appt['start_time'])); 
                    $start_timestamp = date("His", strtotime($appt['start_time'])); 
                }
                if($appt['end_time'] != ''){
                    $end_time = date("H:i:s", strtotime($appt['end_time'])); 
                    $end_timestamp = date("His", strtotime($appt['end_time'])); 
                }
            
                $patient = BlockDoctorAppointment::create([
                    'hospital_detail_id' => $request->hospital_detail_id,
                    'user_id' => $request->doctor_id,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'start_time' => $start_time,
                    'end_time'  => $end_time,
                    'is_recursive' => $is_recursive,
                    'start_timestamp' => $start_timestamp,
                    'end_timestamp' => $end_timestamp,
                    'remarks' => $remarks,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            if($patient->id) {
                return $this->sendResponse(1,200, 'Block appointment added successfully');
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddBlockAppointment:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleBlockAppointment($id) {
        try {
            $patient = ViewBlockDoctorAppointment::SELECT('hospital_detail_id', 'doctor_id', 'start_date', 'end_date', 'start_time', 'end_time', 'is_recursive', 'remarks')->where(['id' => $id])->get();
            if($patient->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $patient);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $patient->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleBlockAppointment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateBlockAppointment(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|integer|exists:users,id,is_active,1',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 2) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            // if($this->VerifyPageAccess('patient', 'is_add') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }

            $appointments = $request->appointment;
            if(empty($appointments)) {
                return $this->sendResponse(0,200, 'Appointment detail is required');
            }

            foreach($appointments as $appt) {
                $start_date = null;
                $end_date = null;
                $star_time = null;
                $end_time = null;
                $remarks = $appt['remarks'];
                $block_id = $appt['block_id'];
                $is_recursive = $appt['is_recursive'];
                if($appt['start_date'] != ''){
                    $start_date = date("Y-m-d", strtotime($appt['start_date'])); 
                }
                if($appt['end_date'] != ''){
                    $end_date = date("Y-m-d", strtotime($appt['end_date'])); 
                }
                if($appt['start_time'] != ''){
                    $start_time = date("H:i:s", strtotime($appt['start_time'])); 
                    $start_timestamp = date("His", strtotime($appt['start_time'])); 
                }
                if($appt['end_time'] != ''){
                    $end_time = date("H:i:s", strtotime($appt['end_time'])); 
                    $end_timestamp = date("His", strtotime($appt['end_time'])); 
                }

                $old_value = ViewBlockDoctorAppointment::SELECT('id', 'hospital_detail_id', 'doctor_id', 'start_date', 'end_date', 'start_time', 'end_time', 'is_recursive', 'remarks')
                ->where(['id' => $block_id])->get();

                //$block_id = $request->block_id;
                $patient = BlockDoctorAppointment::find($block_id);
                $patient->hospital_detail_id = $request->hospital_detail_id;
                $patient->user_id = $request->doctor_id;
                $patient->start_date = $start_date;
                $patient->end_date = $end_date;
                $patient->start_time = $start_time;
                $patient->end_time = $end_time;
                $patient->start_timestamp = $start_timestamp;
                $patient->end_timestamp = $end_timestamp;
                $patient->is_recursive = $is_recursive;
                $patient->remarks = $remarks;
                $patient->updated_by = $request->user_id;
                $patient->updated_at = date('Y-m-d H:i:s');
                $patient->update();

                if($patient->id) {
                    $field_names = [
                        'hospital_detail_id' => 'Location Changed', 
                        'doctor_id' => 'Doctor changed', 
                        'start_date' => 'Appointment start date updated',
                        'end_date' => 'Appointment end date updated', 
                        'start_time' => 'Appointment start time updated', 
                        'end_time' => 'Appointment End time updated',
                        'is_recursive' => 'Appointment days value updated',
                        'remarks' => 'Remarks updated'
                    ];
                    $update_logs = $this->UpdateLogs($request->user_id, $block_id, 'AppointmentLog', 'BlockDoctorAppointment', $old_value, $patient, $field_names);
                }
            }
            return $this->sendResponse(1,200, 'Block appointment details updated successfully');
        
            
        } catch(\Exception $e) {
            Log::debug("API UpdateBlockAppointment:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteBlockAppointment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'block_id'        => 'required|integer',
                'user_id'         => 'required|integer|exists:users,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            if($this->VerifyAuthUser($request->user_id, 2) === true){
                $rec_count = ViewBlockDoctorAppointment::where(['id' => $request->block_id])->get();
                if(count($rec_count) == 1) {
                    $patient = BlockDoctorAppointment::find($request->block_id);
                    $patient->is_active = 0;
                    $patient->updated_by = $request->user_id;
                    $patient->updated_at = date('Y-m-d H:i:s');
                    $patient->update();

                    if($request->block_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $request->block_id, 'AppointmentLog', 'BlockDoctorAppointment', 'Description');
                        return $this->sendResponse(1,200, 'Block appointment deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'block_id', $request->block_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteBlockAppointment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function ViewBlockTempAppointment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|integer|exists:users,id,is_active,1',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1',
                'start_date'        => 'required|date',
                'end_date'          => 'required|date'

            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $doctor_id = $request->doctor_id;
            $hospital_detail_id = $request->hospital_detail_id;
            $start_date = date("Y-m-d", strtotime($request->start_date)); 
            $end_date   = date("Y-m-d", strtotime($request->end_date)); 
            $transaction =  DB::select("SELECT id, hospital_detail_id, doctor_id, 'start_date', 'end_date', 'start_time', 'end_time', is_recursive, doctor_name, location_name, remarks  FROM view_block_temp_appointments WHERE start_date >= '$start_date' AND end_date <= '$end_date' AND hospital_detail_id = $hospital_detail_id AND doctor_id = $doctor_id ");
    
            if(!empty($transaction)) {
                return $this->sendResponse(1,200, 'Success', 'data', $transaction);
            } else {
                return $this->sendResponse(0,200, 'Record not found');
            }
           
        } catch(\Exception $e) {
            Log::debug('API ViewBlockTempAppointment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function AddBlockTempAppointment(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|integer|exists:users,id,role_id,4',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 2) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            // if($this->VerifyPageAccess('patient', 'is_add') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }

            $appointments = $request->appointment;
            if(empty($appointments)) {
                return $this->sendResponse(0,200, 'Block appointment detail is required');
            }

            foreach($appointments as $appt) {
                $start_date = null;
                $end_date = null;
                $star_time = null;
                $end_time = null;
                $remarks = $appt['remarks'];
                $is_recursive = $appt['is_recursive'];
                if($appt['start_date'] != ''){
                    $start_date = date("Y-m-d", strtotime($appt['start_date'])); 
                }
                if($appt['end_date'] != ''){
                    $end_date = date("Y-m-d", strtotime($appt['end_date'])); 
                }
                if($appt['start_time'] != ''){
                    $start_time = date("H:i:s", strtotime($appt['start_time'])); 
                    $start_timestamp = date("His", strtotime($appt['start_time'])); 
                }
                if($appt['end_time'] != ''){
                    $end_time = date("H:i:s", strtotime($appt['end_time'])); 
                    $end_timestamp = date("His", strtotime($appt['end_time'])); 
                }
            
                $patient = BlockTempAppointment::create([
                    'hospital_detail_id' => $request->hospital_detail_id,
                    'user_id' => $request->doctor_id,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'start_time' => $start_time,
                    'end_time'  => $end_time,
                    'is_recursive' => $is_recursive,
                    'start_timestamp' => $start_timestamp,
                    'end_timestamp' => $end_timestamp,
                    'remarks' => $remarks,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            if($patient->id) {
                return $this->sendResponse(1,200, 'Block appointment added successfully');
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddBlockTempAppointment:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleBlockTempAppointment($id) {
        try {
            $patient = ViewBlockTempAppointment::SELECT('hospital_detail_id', 'doctor_id', 'start_date', 'end_date', 'start_time', 'end_time', 'is_recursive', 'remarks')->where(['id' => $id])->get();
            if($patient->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $patient);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $patient->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleBlockTempAppointment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateBlockTempAppointment(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|integer|exists:users,id,is_active,1',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 2) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            // if($this->VerifyPageAccess('patient', 'is_add') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }

            $appointments = $request->appointment;
            if(empty($appointments)) {
                return $this->sendResponse(0,200, 'Block Appointment detail is required');
            }

            foreach($appointments as $appt) {
                $start_date = null;
                $end_date = null;
                $star_time = null;
                $end_time = null;
                $remarks = $appt['remarks'];
                $block_id = $appt['block_id'];
                $is_recursive = $appt['is_recursive'];
                if($appt['start_date'] != ''){
                    $start_date = date("Y-m-d", strtotime($appt['start_date'])); 
                }
                if($appt['end_date'] != ''){
                    $end_date = date("Y-m-d", strtotime($appt['end_date'])); 
                }
                if($appt['start_time'] != ''){
                    $start_time = date("H:i:s", strtotime($appt['start_time'])); 
                    $start_timestamp = date("His", strtotime($appt['start_time'])); 
                }
                if($appt['end_time'] != ''){
                    $end_time = date("H:i:s", strtotime($appt['end_time'])); 
                    $end_timestamp = date("His", strtotime($appt['end_time'])); 
                }
                $old_value = ViewBlockTempAppointment::SELECT('id', 'hospital_detail_id', 'doctor_id', 'start_date', 'end_date', 'start_time', 'end_time', 'is_recursive', 'remarks')
                ->where(['id' => $block_id])->get();

                //$block_id = $request->block_id;
                $patient = BlockTempAppointment::find($block_id);
                $patient->hospital_detail_id = $request->hospital_detail_id;
                $patient->user_id = $request->doctor_id;
                $patient->start_date = $start_date;
                $patient->end_date = $end_date;
                $patient->start_time = $start_time;
                $patient->end_time = $end_time;
                $patient->start_timestamp = $start_timestamp;
                $patient->end_timestamp = $end_timestamp;
                $patient->is_recursive = $is_recursive;
                $patient->remarks = $remarks;
                $patient->updated_by = $request->user_id;
                $patient->updated_at = date('Y-m-d H:i:s');
                $patient->update();

                if($patient->id) {
                    $field_names = [
                        'hospital_detail_id' => 'Location Changed', 
                        'doctor_id' => 'Doctor changed', 
                        'start_date' => 'Appointment start date updated',
                        'end_date' => 'Appointment end date updated', 
                        'start_time' => 'Appointment start time updated', 
                        'end_time' => 'Appointment End time updated',
                        'is_recursive' => 'Appointment days value updated',
                        'remarks' => 'Remarks updated'
                    ];
                    $update_logs = $this->UpdateLogs($request->user_id, $block_id, 'AppointmentLog', 'BlockTempAppointment', $old_value, $patient, $field_names);
                }
            }
            return $this->sendResponse(1,200, 'Block appointment details updated successfully');
            
        } catch(\Exception $e) {
            Log::debug("API UpdateBlockTempAppointment:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteBlockTempAppointment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'block_id'        => 'required|integer',
                'user_id'         => 'required|integer|exists:users,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            if($this->VerifyAuthUser($request->user_id, 2) === true){
                $rec_count = ViewBlockTempAppointment::where(['id' => $request->block_id])->get();
                if(count($rec_count) == 1) {
                    $patient = BlockTempAppointment::find($request->block_id);
                    $patient->is_active = 0;
                    $patient->updated_by = $request->user_id;
                    $patient->updated_at = date('Y-m-d H:i:s');
                    $patient->update();

                    if($request->block_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $request->block_id, 'AppointmentLog', 'BlockTempAppointment', 'Description');
                        return $this->sendResponse(1,200, 'Block appointment deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'block_id', $request->block_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteBlockTempAppointment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteAppointment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'appointment_id'  => 'required|integer',
                'user_id'         => 'required|integer|exists:users,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            $app_id = $request->appointment_id;
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Appointment::where(['id' => $app_id])->get();
                if(count($rec_count) == 1) {
                    $delete  = DB::table('appointments')->where('id', $app_id)->delete();
                    $delete_logs = $this->DeleteLogs($request->user_id, $app_id, 'AppointmentLog', 'Appointment', 'Description');
                    
                    return $this->sendResponse(1,200, 'Appointment cancelled successfully');
                    
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteAppointment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetAppointmentLogs(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'log_id'            => 'required|integer',
                'log_name'          => 'required|in:Appointment,BlockDoctorAppointment,BlockTempAppointment'

            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 2) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $log_id = $request->log_id;
            $log_name = $request->log_name;

            $list = AppointmentLog::SELECT('id', 'description', 'table_id', 'updated_by', 'updated_at')
                    ->WHERE(['table_id' => $log_id, 'table_name' => $log_name, 'action_type'=> 'Update']) ->ORDERBY('id', 'DESC')->LIMIT(60)->get();

            return $this->sendResponse(1, 200, 'Success', 'data', $list);

        } catch(\Exception $e) {
            Log::debug('API GetExamList:: '.$e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function AddBlockPatientAppointment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|array',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1',
                'patient_detail_id' => 'required|integer|exists:patient_details,id,is_active,1',
            ]);
    
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 2) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            // Check for existing appointments for each doctor_id
            foreach ($request->doctor_id as $doctorId) {
                $existingAppointment = BlockPatientAppointment::where('patient_detail_id', $request->patient_detail_id)
                    ->where('doctor_id', $doctorId)
                    ->exists();
    
                if ($existingAppointment) {
                    return $this->sendResponse(0, 200, "Patient already blocked for doctor ID {$doctorId}. Please try again.");
                }
            }
    
            // Create block appointment for each doctor_id
            $success = true;
            foreach ($request->doctor_id as $doctorId) {
                $blockpatient = BlockPatientAppointment::create([
                    'hospital_detail_id' => $request->hospital_detail_id,
                    'doctor_id'         => $doctorId,
                    'patient_detail_id' => $request->patient_detail_id,
                    'block_reason'      => $request->block_reason,
                    'block_status'      => $request->block_status,
                    'patient_notes'      => $request->patient_notes,
                    'block_status_color' => $request->block_status_color,
                    'user_id'           => $request->user_id,
                    'created_by'        => $request->user_id,
                    'updated_by'        => $request->user_id,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s')
                ]);
    
                if (!$blockpatient->id) {
                    $success = false;
                    break;
                }
            }
    
            if ($success) {
                return $this->sendResponse(1, 200, 'Block Patient Appointment successfully created for all doctors');
            } else {
                return $this->sendResponse(0, 200, 'Something went wrong while creating block appointments. Please try again.');
            }
    
        } catch (\Exception $e) {
            Log::debug('API AddBlockPatientAppointment :: ' . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong. Please try again later.', 'error', $e->getMessage());
        }
    }
    
    // public function UpdateBlockPatientAppointment(Request $request) {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'user_id'           => 'required|integer|exists:users,id,is_active,1',
    //             'doctor_id'         => 'required|array',
    //             'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1',
    //             'patient_detail_id' => 'required|integer|exists:patient_details,id,is_active,1',
    //             'block_reason'      => 'nullable|string|max:255',
    //             'block_status'      => 'nullable|string|max:255',
    //             'patient_notes'      => 'nullable|string|max:255',
    //             'block_status_color'      => 'nullable|string|max:255',
    //         ]);
    
    //         if ($validator->fails()) {
    //             return $this->sendResponse(0, 200, $validator->errors()->first(), '');
    //         }
    
    //         if ($this->VerifyAuthUser($request->user_id, 2) === false) {
    //             return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
    //         }
    
    //         // Update block appointments for each doctor_id
    //         $success = true;
    //         foreach ($request->doctor_id as $doctorId) {
    //             $blockAppointment = BlockPatientAppointment::where('patient_detail_id', $request->patient_detail_id)
    //                 ->where('doctor_id', $doctorId)
    //                 ->where('hospital_detail_id', $request->hospital_detail_id)
    //                 ->first();
    
    //             if (!$blockAppointment) {
    //                 return $this->sendResponse(0, 200, "No block appointment found for doctor ID {$doctorId}. Please check the details.");
    //             }
    
    //             $updateData = [
    //                 'updated_by' => $request->user_id,
    //                 'updated_at' => date('Y-m-d H:i:s')
    //             ];
    
    //             if (!is_null($request->block_reason)) {
    //                 $updateData['block_reason'] = $request->block_reason;
    //             }
    
    //             if (!is_null($request->block_status)) {
    //                 $updateData['block_status'] = $request->block_status;
    //             }
    
    //             $updated = BlockPatientAppointment::where('patient_detail_id', $request->patient_detail_id)
    //                 ->where('doctor_id', $doctorId)
    //                 ->where('hospital_detail_id', $request->hospital_detail_id)
    //                 ->update($updateData);
    
    //             if (!$updated) {
    //                 $success = false;
    //                 break;
    //             }
    //         }
    
    //         if ($success) {
    //             return $this->sendResponse(1, 200, 'Block Patient Appointment successfully updated for all doctors');
    //         } else {
    //             return $this->sendResponse(0, 200, 'Something went wrong while updating block appointments. Please try again.');
    //         }
    
    //     } catch (\Exception $e) {
    //         Log::debug('API UpdateBlockPatientAppointment :: ' . $e->getMessage());
    //         return $this->sendResponse(0, 200, 'Something went wrong. Please try again later.', 'error', $e->getMessage());
    //     }
    // }
    
    public function UpdateBlockPatientAppointment(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|array',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1',
                'patient_detail_id' => 'required|integer|exists:patient_details,id,is_active,1',
                'block_reason'      => 'nullable|string|max:255',
                'block_status'      => 'nullable|int:1',
                'patient_notes'     => 'nullable|string|max:1000',
                'block_status_color'=> 'nullable|string|max:50',
            ]);
    
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 2) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            $success = true;
            $operationCounts = ['added' => 0, 'updated' => 0];
    
            foreach ($request->doctor_id as $doctorId) {
                $blockAppointment = BlockPatientAppointment::where('patient_detail_id', $request->patient_detail_id)
                    ->where('doctor_id', $doctorId)
                    ->where('hospital_detail_id', $request->hospital_detail_id)
                    ->first();
    
                if ($blockAppointment) {
                    // Update existing appointment
                    $updateData = [
                        'updated_by' => $request->user_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
    
                    foreach (['block_reason', 'block_status', 'patient_notes', 'block_status_color'] as $field) {
                        if (!is_null($request->$field)) {
                            $updateData[$field] = $request->$field;
                        }
                    }
    
                    $updated = BlockPatientAppointment::where('patient_detail_id', $request->patient_detail_id)
                        ->where('doctor_id', $doctorId)
                        ->where('hospital_detail_id', $request->hospital_detail_id)
                        ->update($updateData);
    
                    if ($updated) {
                        $operationCounts['updated']++;
                    } else {
                        $success = false;
                        break;
                    }
                } else {
                    // Create new appointment
                    $blockpatient = BlockPatientAppointment::create([
                        'hospital_detail_id' => $request->hospital_detail_id,
                        'doctor_id'         => $doctorId,
                        'patient_detail_id' => $request->patient_detail_id,
                        'block_reason'      => $request->block_reason,
                        'block_status'      => $request->block_status,
                        'patient_notes'     => $request->patient_notes,
                        'block_status_color'=> $request->block_status_color,
                        'user_id'           => $request->user_id,
                        'created_by'        => $request->user_id,
                        'updated_by'        => $request->user_id,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s')
                    ]);
    
                    if ($blockpatient->id) {
                        $operationCounts['added']++;
                    } else {
                        $success = false;
                        break;
                    }
                }
            }
    
            if ($success) {
                $message = ' Patient Block updated successfully'; 
                // $message = 'Block Patient Appointments processed successfully: ' . 
                //           $operationCounts['added'] . ' added, ' . 
                //           $operationCounts['updated'] . ' updated';
                return $this->sendResponse(1, 200, $message);
            } else {
                return $this->sendResponse(0, 200, 'Something went wrong while processing block appointments. Please try again.');
            }
    
        } catch (\Exception $e) {
            Log::debug('API UpdateBlockPatientAppointment :: ' . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong. Please try again later.', 'error', $e->getMessage());
        }
    }
    
    // public function ViewBlockPatientByDoctor(Request $request) {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'user_id'           => 'required|integer|exists:users,id,is_active,1',
    //             'doctor_id'         => 'required|integer|exists:users,id,is_active,1',
    //             'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1'

    //         ]);
    //         if ($validator->fails()) {
    //             return $this->sendResponse(0, 200, $validator->errors()->first(), '');
    //         }
    //         if($this->VerifyAuthUser($request->user_id, 0) === false){
    //             return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
    //         }

    //         $doctor_id = $request->doctor_id;
    //         $hospital_detail_id = $request->hospital_detail_id;
            
    //         $blockPatient = ViewBlockPatientAppointment::SELECT('id', 'hospital_detail_id', 'patient_detail_id', 'first_name', 'middle_name', 'last_name', 'doctor_id', 'doctor_name', 'block_reason', 'block_status', 'patient_notes', 'block_status_color', 'location_name')
    //                 ->WHERE(['doctor_id' => $doctor_id, 'hospital_detail_id' => $hospital_detail_id])->get();
            
    //         if(!empty($blockPatient)) {
    //             return $this->sendResponse(1,200, 'Success', 'data', $blockPatient);
    //         } else {
    //             return $this->sendResponse(0,200, 'Record not found');
    //         }
           
    //     } catch(\Exception $e) {
    //         Log::debug('API ViewBlockTempAppointment :: '.$e->getMessage());
    //         return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
    //     }
    // }
    
    public function ViewBlockPatientByDoctor(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|integer|exists:users,id,is_active,1',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if ($this->VerifyAuthUser($request->user_id, 0) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            $doctor_id = $request->doctor_id;
            $hospital_detail_id = $request->hospital_detail_id;
    
            // Fetch blocked patients with their details
            $blockPatient = ViewBlockPatientAppointment::select('id', 'hospital_detail_id', 'patient_detail_id', 'first_name', 'middle_name', 'last_name', 'doctor_id', 'doctor_name', 'block_reason', 'block_status', 'patient_notes', 'block_status_color', 'location_name')
                ->where(['doctor_id' => $doctor_id, 'hospital_detail_id' => $hospital_detail_id])
                ->get();
    
            // Add array of doctors for whom each patient is blocked
            $blockPatient = $blockPatient->map(function ($patient) use ($hospital_detail_id) {
                $blockedDoctors = ViewBlockPatientAppointment::select('doctor_id', 'doctor_name', 'block_status', 'block_status_color')
                    ->where(['patient_detail_id' => $patient->patient_detail_id, 'hospital_detail_id' => $hospital_detail_id])
                    ->distinct()
                    ->get()
                    ->toArray();
                $patient->blocked_doctors = $blockedDoctors;
                return $patient;
            });
    
            if ($blockPatient->isNotEmpty()) {
                return $this->sendResponse(1, 200, 'Success', 'data', $blockPatient);
            } else {
                return $this->sendResponse(0, 200, 'Record not found');
            }
    
        } catch (\Exception $e) {
            Log::debug('API ViewBlockPatientByDoctor :: ' . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

}