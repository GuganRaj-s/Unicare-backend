<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\ViewAppointment;
use App\PatientEnquiry;
use App\ViewPatientEnquiry;
use App\PhoneEnquiry;
use App\ViewPhoneEnquiry;
use App\WaitingList;
use App\ViewWaitingList;
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

class PatientEnquiryController extends BaseController
{

    public function ViewPatientEnquiry(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'app_date'          => 'required|date',
                'search_field'      => 'nullable|string|in:first_name,last_name,middle_name,contact_no,doctor_name,staff_name,referral_doctor_name,department_name',
                'search_value'      => 'nullable|string|min:3|max:20',

            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $app_date = null;
            if($request->app_date != ''){
                $app_date = date("Y-m-d", strtotime($request->app_date)); 
            }
            
            if($request->search_field == '') {
                $patient = ViewPatientEnquiry::SELECT('patient_id', 'first_name', 'last_name', 'middle_name','contact_no','doctor_name','staff_name','referral_doctor_name','department_name','enquiry_reason','appointment_date','comments','time_interval','from_time','to_time')
                ->where(['appointment_date' => $app_date])->get();
            } else {
                $search_field = $request->search_field;
                $search_value = $request->search_value;
                $patient = ViewPatientEnquiry::SELECT('patient_id', 'first_name', 'last_name', 'middle_name','contact_no','doctor_name','staff_name','referral_doctor_name','department_name','enquiry_reason','appointment_date','comments','time_interval','from_time','to_time')
                ->where(['appointment_date' => $app_date])
                ->where($search_field, 'LIKE', "%{$search_value}%") 
                ->get();
            }
                
            if($patient->count() != 0) {
                return $this->sendResponse(1,200, 'Success', 'data', $patient);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $patient->count());
            }
        } catch(\Exception $e) {
            Log::debug('API ViewPatientEnquiry :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function AddPatientEnquiry(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'first_name'        => 'required|string|min:3|max:30',
                'last_name'         => 'required|string|min:3|max:30',
                'middle_name'       => 'nullable|string|min:3|max:30',
                'contact_no'        => 'required|string|min:0|max:20',
                'doctor_id'         => 'required|integer|exists:users,id',
                'staff_id'          => 'nullable|integer|exists:users,id',
                'referral_doctor_id' => 'nullable|integer|exists:referral_doctors,id',
                'department_id'      => 'required|integer|exists:departments,id',
                'enquiry_reason_id'  => 'required|integer|exists:enquiry_reasons,id',
                'appointment_date'   => 'required|date',
                'comments'           => 'nullable|string|min:3|max:250',
                'time_interval'      => 'nullable|integer|min:3|max:30',
                'to_time'            => 'nullable|string|min:3|max:30',
                'from_time'          => 'nullable|string|min:3|max:30',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            // if($this->VerifyPageAccess('patient', 'is_add') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }

            $appointment_date = null;
            if($request->appointment_date != ''){
                $appointment_date = date("Y-m-d", strtotime($request->appointment_date)); 
            }
            
            $new_patient = PatientEnquiry::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'contact_no' => $request->contact_no,
                'doctor_id' => $request->doctor_id,
                'staff_id' => $request->staff_id,
                'referral_doctor_id' => $request->referral_doctor_id,
                'department_id' => $request->department_id,
                'enquiry_reason_id' => $request->enquiry_reason_id,
                'appointment_date' => $appointment_date,
                'comments' => $request->comments,
                'time_interval' => $request->time_interval,
                'from_time' => $request->from_time,
                'to_time' => $request->to_time,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($new_patient->id) {
                return $this->sendResponse(1,200, 'Patient details created successfully', 'patient_id', $new_patient->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddPatientEnquiry:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSinglePatientEnquiry($id) {
        try {
            $patient = ViewPatientEnquiry::where(['patient_id' => $id])->get();
            if($patient->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $patient);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $patient->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSinglePatientEnquiry :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdatePatientEnquiry(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'patient_id'        => 'required|integer|exists:patient_enquiries,id,is_active,1',
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'first_name'        => 'required|string|min:3|max:30',
                'last_name'         => 'required|string|min:3|max:30',
                'middle_name'       => 'nullable|string|min:3|max:30',
                'contact_no'        => 'required|string|min:0|max:20',
                'doctor_id'         => 'required|integer|exists:users,id',
                'staff_id'          => 'nullable|integer|exists:users,id',
                'referral_doctor_id' => 'nullable|integer|exists:referral_doctors,id',
                'department_id'      => 'required|integer|exists:departments,id',
                'enquiry_reason_id'  => 'required|integer|exists:enquiry_reasons,id',
                'appointment_date'   => 'required|date',
                'comments'           => 'nullable|string|min:3|max:250',
                'time_interval'      => 'nullable|integer|min:3|max:30',
                'to_time'            => 'nullable|string|min:3|max:30',
                'from_time'          => 'nullable|string|min:3|max:30',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            // if($this->VerifyPageAccess('patient', 'is_add') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }
            $old_value = PatientEnquiry::SELECT('id', 'name', 'contact_no', 'staff_id', 'doctor_id', 'referral_doctor_id', 'department_id', 'enquiry_reason_id', 'appointment_date', 'comments', 'time_interval', 'from_time', 'to_time')
            ->where(['id' => $request->patient_id])->get();

            $appointment_date = null;
            if($request->appointment_date != ''){
                $appointment_date = date("Y-m-d", strtotime($request->appointment_date)); 
            }
            $patient_id = $request->patient_id;
            $patient = PatientEnquiry::find($patient_id);
            $patient->first_name = $request->first_name;
            $patient->last_name = $request->last_name;
            $patient->middle_name = $request->middle_name;
            $patient->contact_no = $request->contact_no;
            $patient->doctor_id = $request->doctor_id;
            $patient->staff_id = $request->staff_id;
            $patient->referral_doctor_id = $request->referral_doctor_id;
            $patient->department_id = $request->department_id;
            $patient->enquiry_reason_id = $request->enquiry_reason_id;
            $patient->appointment_date = $appointment_date;
            $patient->comments = $request->comments;
            $patient->time_interval = $request->time_interval;
            $patient->from_time = $request->from_time;
            $patient->to_time = $request->to_time;
            $patient->updated_by = $request->user_id;
            $patient->updated_at = date('Y-m-d H:i:s');
            $patient->update();

            if($patient->id) {
                $field_names = [
                    'first_name' => 'Patient first name updated', 
                    'last_name' => 'Patient last name updated', 
                    'middle_name' => 'Patient middle name updated', 
                    'contact_no' => 'Contact number updated', 
                    'doctor_id' => 'Doctor changed', 
                    'staff_id' => 'Staff changed', 
                    'referral_doctor_id' => 'Referral doctor updated', 
                    'department_id' => 'Department updated', 
                    'enquiry_reason_id' => 'Enquiry Reason changed', 
                    'appointment_date' => 'Appointment date updated', 
                    'comments' => 'Comments updated', 
                    'time_interval' => 'Time interval updated', 
                    'from_time' => 'From time updated', 
                    'to_time' => 'To time updated'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $patient_id, 'PatientEnquiry', 'PatientEnquiry', $old_value, $patient, $field_names);

                return $this->sendResponse(1,200, 'Patient details updated successfully', 'patient_id', $patient->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdatePatientEnquiry:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeletePatientEnquiry(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'patient_id'        => 'required|integer|exists:patient_enquiries,id,is_active,1',
                'user_id'           => 'required|integer|exists:users,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = PatientEnquiry::where(['id' => $request->patient_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $patient = PatientEnquiry::find($request->patient_id);
                    $patient->is_active = 0;
                    $patient->updated_by = $request->user_id;
                    $patient->updated_at = date('Y-m-d H:i:s');
                    $patient->update();

                    if($request->patient_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $patient->id, 'PatientEnquiry', 'PatientEnquiry', 'Description');
                        return $this->sendResponse(1,200, 'Patient deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'patient_id', $request->patient_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeletePatientEnquiry :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function AddPhoneEnquiry(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'         => 'required|integer|exists:users,id,role_id,4,is_active,1',
                'staff_id'          => 'nullable|integer|exists:users,id,role_id,5,is_active,1',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1',
                'name'              => 'required|string|min:3|max:50',
                'first_name'        => 'nullable|string|min:3|max:50',
                'middle_name'       => 'nullable|string|min:1|max:50',
                'last_name'         => 'nullable|string|min:1|max:50',
                'have_emirates_ids' => 'required|in:1,0',
                'primary_number'    => 'required|string|min:3|max:20',
                'secondary_number'  => 'nullable|string|min:0|max:20',
                'whatsup_number'    => 'required|string|in:Primary,Secondary',
                'enquiry_service_id' => 'required|integer|exists:enquiry_services,id',
                'department_id'      => 'required|integer|exists:departments,id',
                'enquiry_reason_id'  => 'required|integer|exists:enquiry_reasons,id',
                'appointment_date'   => 'required|date',
                'comments'           => 'nullable|string|min:3|max:250',
                'time_interval'      => 'required|integer|min:1',
                'group_id'          => 'nullable|string|min:0|max:100',
                'from_time'          => 'required|date_format:H:i:s',
                'to_time'            => 'required|date_format:H:i:s|after:from_time',
                'appointment_status_id' => 'required|integer|exists:appointment_statuses,id,is_active,1',
                'color_code'        => 'required|string|min:6|max:20',
                'isDoubleBooking'   => 'required|in:0,1',
                'idDoubleBookingExist' => 'required|in:0,1',
                'isBetweenSlot'     => 'required|in:0,1',
                'checkIn' => 'nullable|date_format:H:i:s',
                'checkOut' => 'nullable|date_format:H:i:s'
            ]);
    
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 0) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            // Format appointment date
            $appointment_date = date("Y-m-d", strtotime($request->appointment_date));
    
            // Calculate start and end times
            $time_interval = $request->time_interval;
            $app_start = date("Y-m-d H:i:s", strtotime($appointment_date . ' ' . $request->from_time));
    
            $time = new DateTime($app_start);
            $time->add(new DateInterval('PT' . $time_interval . 'M'));
            $app_end = $time->format('Y-m-d H:i:s');
    
            // Ensure to_time matches calculated end time
            $expected_to_time = $time->format('H:i:s');
            if ($request->to_time !== $expected_to_time) {
                return $this->sendResponse(0, 200, 'The to_time must match the from_time plus the time_interval.', '');
            }
    
            // Convert times to timestamps for comparison
            $start_timestamp = strtotime($app_start);
            $end_timestamp = strtotime($app_end);
    
            // Format for database comparison
            $time_start = (new DateTime($app_start))->format('YmdHis');
            $time_end = (new DateTime($app_end))->format('YmdHis');
            
            
                // Check for blocked doctor appointments
                $block_date = date("Y-m-d", $start_timestamp);
                $block_time_1 = date("His", $start_timestamp);
                $block_time_2 = date("His", $end_timestamp);
    
            // Check conditions based on isDoubleBooking, idDoubleBookingExist, and isBetweenSlot
            if ($request->isDoubleBooking == 0 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 0) {
                // Case 1: Check all conditions
                // Check for overlapping appointments
                $IsExist = ViewAppointment::where([
                    'doctor_id' => $request->doctor_id,
                    'hospital_detail_id' => $request->hospital_detail_id
                ])
                ->where(function ($query) use ($time_start, $time_end) {
                    $query->where('time_start', '<', $time_end)
                          ->where('time_end', '>', $time_start);
                })
                ->count();
    
                if ($IsExist > 0) {
                    return $this->sendResponse(0, 200, 'This slot already booked for another patient');
                }
    
                $block_doctor = DB::select("SELECT count(*) as 'block_count' FROM view_block_doctor_appointments WHERE doctor_id = ? AND hospital_detail_id = ? AND start_date <= ? AND end_date >= ? AND ((start_timestamp < ? AND end_timestamp > ?) OR (start_timestamp = ? AND end_timestamp = ?))", [
                    $request->doctor_id,
                    $request->hospital_detail_id,
                    $block_date,
                    $block_date,
                    $block_time_2,
                    $block_time_1,
                    $block_time_1,
                    $block_time_2
                ]);
    
                if ($block_doctor[0]->block_count > 0) {
                    return $this->sendResponse(0, 200, 'This slot already blocked by doctor');
                }
    
                // Check for temporary blocked appointments
                $block_temp = DB::select("SELECT count(*) as 'block_count' FROM view_block_temp_appointments WHERE doctor_id = ? AND hospital_detail_id = ? AND start_date <= ? AND end_date >= ? AND ((start_timestamp < ? AND end_timestamp > ?) OR (start_timestamp = ? AND end_timestamp = ?))", [
                    $request->doctor_id,
                    $request->hospital_detail_id,
                    $block_date,
                    $block_date,
                    $block_time_2,
                    $block_time_1,
                    $block_time_1,
                    $block_time_2
                ]);
    
                if ($block_temp[0]->block_count > 0) {
                    return $this->sendResponse(0, 200, 'This slot temporarily blocked by doctor');
                }
    
                // Check for overlapping phone enquiries
                $block_phone = DB::select("SELECT count(*) as 'block_count' FROM view_phone_enquiry WHERE doctor_id = ? AND hospital_detail_id = ? AND appointment_date = ? AND ((app_start_time < ? AND app_end_time > ?) OR (app_start_time = ? AND app_end_time = ?))", [
                    $request->doctor_id,
                    $request->hospital_detail_id,
                    $block_date,
                    $block_time_2,
                    $block_time_1,
                    $block_time_1,
                    $block_time_2
                ]);
    
                if ($block_phone[0]->block_count > 0) {
                    return $this->sendResponse(0, 200, 'This slot already booked for another patient via phone enquiry');
                }
            } elseif ($request->isDoubleBooking == 1 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 0) {
                // Case 2: Check if slot is booked when isDoubleBooking is 1, and prevent booking if any existing phone enquiry exists
                $IsExist = PhoneEnquiry::where([
                    'doctor_id' => $request->doctor_id,
                    'hospital_detail_id' => $request->hospital_detail_id,
                    'appointment_date' => $appointment_date
                ])
                ->where(function ($query) use ($block_time_1, $block_time_2) {
                    $query->where('app_start_time', '<', $block_time_2)
                          ->where('isDoubleBooking', '=', 1)
                          ->where('app_end_time', '>', $block_time_1);
                })
                ->count();
    
                if ($IsExist > 0) {
                    return $this->sendResponse(0, 200, 'This slot already booked for another patient via phone enquiry');
                }
            } elseif ($request->isDoubleBooking == 1 && $request->idDoubleBookingExist == 1 && $request->isBetweenSlot == 0) {
                // Case 3: No need to check any conditions
                // Proceed to create phone enquiry
            } elseif ($request->isDoubleBooking == 0 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 1) {
                // Case 4: No need to check any conditions
                // Proceed to create phone enquiry
            }
    
            // Create new phone enquiry
            $new_patient = PhoneEnquiry::create([
                'name'              => $request->name,
                'first_name'        => $request->first_name,
                'middle_name'       => $request->middle_name,
                'last_name'         => $request->last_name,
                'hospital_detail_id' => $request->hospital_detail_id,
                'have_emirates_ids' => $request->have_emirates_ids,
                'primary_number'    => $request->primary_number,
                'secondary_number'  => $request->secondary_number,
                'whatsup_number'    => $request->whatsup_number,
                'doctor_id'         => $request->doctor_id,
                'staff_id'          => $request->staff_id,
                'enquiry_service_id' => $request->enquiry_service_id,
                'department_id'      => $request->department_id,
                'enquiry_reason_id'  => $request->enquiry_reason_id,
                'appointment_date'   => $appointment_date,
                'comments'           => $request->comments,
                'group_id'           => $request->group_id,
                'time_interval'      => $request->time_interval,
                'appointment_status_id' => $request->appointment_status_id,
                'isDoubleBooking'   => $request->isDoubleBooking,
                'idDoubleBookingExist' => $request->idDoubleBookingExist,
                'isBetweenSlot'     => $request->isBetweenSlot,
                'checkIn'     => $request->checkIn,
                'checkOut'     => $request->checkOut,
                'color_code'        => $request->color_code,
                'from_time'         => $request->from_time,
                'to_time'           => $request->to_time,
                'app_start_time'    => date("His", $start_timestamp),
                'app_end_time'      => date("His", $end_timestamp),
                'created_by'        => $request->user_id,
                'updated_by'        => $request->user_id,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s')
            ]);
    
            if ($new_patient->id) {
                return $this->sendResponse(1, 200, 'Created successfully', 'patient_id', $new_patient->id);
            } else {
                return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.');
            }
    
        } catch (\Exception $e) {
            Log::debug("API AddPhoneEnquiry:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function UpdatePhoneEnquiry(Request $request) {
        try {
            // Validate inputs
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'id'                => 'required|integer|exists:phone_enquiries,id,is_active,1',
                'doctor_id'         => 'required|integer|exists:users,id,role_id,4,is_active,1',
                'staff_id'          => 'nullable|integer|exists:users,id,role_id,5,is_active,1',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1',
                'name'              => 'required|string|min:3|max:50',
                'first_name'        => 'nullable|string|min:3|max:50',
                'middle_name'       => 'nullable|string|min:1|max:50',
                'last_name'         => 'nullable|string|min:1|max:50',
                'have_emirates_ids' => 'required|in:1,0',
                'primary_number'    => 'required|string|min:3|max:20',
                'secondary_number'  => 'nullable|string|min:0|max:20',
                'whatsup_number'    => 'required|string|in:Primary,Secondary',
                'enquiry_service_id'=> 'required|integer|exists:enquiry_services,id',
                'department_id'     => 'required|integer|exists:departments,id',
                'enquiry_reason_id' => 'required|integer|exists:enquiry_reasons,id',
                'appointment_date'  => 'required|date',
                'comments'          => 'nullable|string|min:3|max:250',
                'time_interval'     => 'required|integer|min:1',
                'group_id'          => 'nullable|string|max:100',
                'from_time'         => 'required|date_format:H:i:s',
                'to_time'           => 'required|date_format:H:i:s|after:from_time',
                'appointment_status_id' => 'required|integer|exists:appointment_statuses,id,is_active,1',
                'color_code'        => 'required|string|min:6|max:20',
                'isDoubleBooking'   => 'required|in:0,1',
                'idDoubleBookingExist' => 'required|in:0,1',
                'isBetweenSlot'     => 'required|in:0,1',
                'checkIn' => 'nullable|date_format:H:i:s',
                'checkOut' => 'nullable|date_format:H:i:s'
            ]);
    
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if (!$this->VerifyAuthUser($request->user_id, 0)) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
            
            $id = $request->id;
    
            if (!$id || !is_numeric($id)) {
                return $this->sendResponse(0, 200, 'Invalid or missing enquiry ID.');
            }
            
            // Prepare time logic
            $appointment_date = date("Y-m-d", strtotime($request->appointment_date));
            $time_interval = $request->time_interval;
    
            $app_start = date("Y-m-d H:i:s", strtotime($appointment_date . ' ' . $request->from_time));
            $time = new DateTime($app_start);
            $time->add(new DateInterval('PT' . $time_interval . 'M'));
            $app_end = $time->format('Y-m-d H:i:s');
    
            $expected_to_time = $time->format('H:i:s');
            if ($request->to_time !== $expected_to_time) {
                return $this->sendResponse(0, 200, 'The to_time must match the from_time plus the time_interval.', '');
            }
    
            $start_timestamp = strtotime($app_start);
            $end_timestamp = strtotime($app_end);
            $time_start = date('YmdHis', $start_timestamp);
            $time_end = date('YmdHis', $end_timestamp);
            $block_date = date('Y-m-d', $start_timestamp);
            $block_time_1 = date('His', $start_timestamp);
            $block_time_2 = date('His', $end_timestamp);
    
            // Check conditions based on isDoubleBooking, idDoubleBookingExist, and isBetweenSlot
            if ($request->isDoubleBooking == 0 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 0) {
                // Case 1: Check all conditions
                // Check overlapping appointments
                $IsExist = ViewAppointment::where('doctor_id', $request->doctor_id)
                    ->where('hospital_detail_id', $request->hospital_detail_id)
                    ->where('id', '<>', $id)
                    ->where(function ($query) use ($time_start, $time_end) {
                        $query->where('time_start', '<', $time_end)
                              ->where('time_end', '>', $time_start);
                    })->count();
    
                if ($IsExist > 0) {
                    return $this->sendResponse(0, 200, 'This slot already booked for another patient');
                }
    
                // Check doctor block
                $block_doctor = DB::select("SELECT count(*) as block_count FROM view_block_doctor_appointments 
                    WHERE doctor_id = ? AND hospital_detail_id = ? AND start_date <= ? AND end_date >= ?
                    AND ((start_timestamp < ? AND end_timestamp > ?) OR (start_timestamp = ? AND end_timestamp = ?))", [
                        $request->doctor_id,
                        $request->hospital_detail_id,
                        $block_date,
                        $block_date,
                        $block_time_2,
                        $block_time_1,
                        $block_time_1,
                        $block_time_2
                    ]);
    
                if ($block_doctor[0]->block_count > 0) {
                    return $this->sendResponse(0, 200, 'This slot is blocked by the doctor');
                }
    
                // Check temporary block
                $block_temp = DB::select("SELECT count(*) as block_count FROM view_block_temp_appointments 
                    WHERE doctor_id = ? AND hospital_detail_id = ? AND start_date <= ? AND end_date >= ?
                    AND ((start_timestamp < ? AND end_timestamp > ?) OR (start_timestamp = ? AND end_timestamp = ?))", [
                        $request->doctor_id,
                        $request->hospital_detail_id,
                        $block_date,
                        $block_date,
                        $block_time_2,
                        $block_time_1,
                        $block_time_1,
                        $block_time_2
                    ]);
    
                if ($block_temp[0]->block_count > 0) {
                    return $this->sendResponse(0, 200, 'This slot is temporarily blocked');
                }
    
                // Check phone enquiry overlap
                $block_phone = DB::select("SELECT count(*) as block_count FROM view_phone_enquiry 
                    WHERE doctor_id = ? AND hospital_detail_id = ? AND appointment_date = ? AND id != ? 
                    AND ((app_start_time < ? AND app_end_time > ?) OR (app_start_time = ? AND app_end_time = ?))", [
                        $request->doctor_id,
                        $request->hospital_detail_id,
                        $block_date,
                        $id,
                        $block_time_2,
                        $block_time_1,
                        $block_time_1,
                        $block_time_2
                    ]);
    
                if ($block_phone[0]->block_count > 0) {
                    return $this->sendResponse(0, 200, 'This slot is already booked via phone enquiry');
                }
            } elseif ($request->isDoubleBooking == 1 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 0) {
                // Case 2: Check if slot is booked when isDoubleBooking is 1, and prevent booking if any existing phone enquiry exists
                $IsExist = PhoneEnquiry::where([
                    'doctor_id' => $request->doctor_id,
                    'hospital_detail_id' => $request->hospital_detail_id,
                    'appointment_date' => $block_date,
                    'id' => ['<>', $id]
                ])
                ->where(function ($query) use ($block_time_1, $block_time_2) {
                    $query->where('app_start_time', '<', $block_time_2)
                          ->where('isDoubleBooking', '=', 1)
                          ->where('app_end_time', '>', $block_time_1);
                })
                ->count();
    
                if ($IsExist > 0) {
                    return $this->sendResponse(0, 200, 'This slot already booked for another patient via phone enquiry');
                }
            } elseif ($request->isDoubleBooking == 1 && $request->idDoubleBookingExist == 1 && $request->isBetweenSlot == 0) {
                // Case 3: No need to check any conditions
                // Proceed to update phone enquiry
            } elseif ($request->isDoubleBooking == 0 && $request->idDoubleBookingExist == 0 && $request->isBetweenSlot == 1) {
                // Case 4: No need to check any conditions
                // Proceed to update phone enquiry
            }
    
            // Update phone enquiry
            $update = PhoneEnquiry::where('id', $id)->update([
                'name'               => $request->name,
                'first_name'         => $request->first_name,
                'middle_name'        => $request->middle_name,
                'last_name'          => $request->last_name,
                'hospital_detail_id' => $request->hospital_detail_id,
                'have_emirates_ids'  => $request->have_emirates_ids,
                'primary_number'     => $request->primary_number,
                'secondary_number'   => $request->secondary_number,
                'whatsup_number'     => $request->whatsup_number,
                'doctor_id'          => $request->doctor_id,
                'staff_id'           => $request->staff_id,
                'enquiry_service_id' => $request->enquiry_service_id,
                'department_id'      => $request->department_id,
                'enquiry_reason_id'  => $request->enquiry_reason_id,
                'appointment_date'   => $appointment_date,
                'comments'           => $request->comments,
                'group_id'           => $request->group_id,
                'time_interval'      => $request->time_interval,
                'appointment_status_id' => $request->appointment_status_id,
                'isDoubleBooking'    => $request->isDoubleBooking,
                'idDoubleBookingExist' => $request->idDoubleBookingExist,
                'isBetweenSlot'      => $request->isBetweenSlot,
                'checkIn'      => $request->checkIn,
                'checkOut'      => $request->checkOut,
                'color_code'         => $request->color_code,
                'from_time'          => $request->from_time,
                'to_time'            => $request->to_time,
                'app_start_time'     => date("His", $start_timestamp),
                'app_end_time'       => date("His", $end_timestamp),
                'updated_by'         => $request->user_id,
                'updated_at'         => now(),
            ]);
    
            if ($update) {
                return $this->sendResponse(1, 200, 'Phone enquiry updated successfully');
            } else {
                return $this->sendResponse(0, 200, 'No changes made or invalid ID');
            }
    
        } catch (\Exception $e) {
            Log::debug("API UpdatePhoneEnquiry:: " . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong. Try again later.', 'error', $e->getMessage());
        }
    }

    
    public function AddWaitingList(Request $request) {
        try {
            // Log request data for debugging
            Log::debug("AddWaitingList Request Data: ", $request->all());
    
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id,is_active,1',
                'firstname'         => 'required|string|min:3|max:100',
                'middleName'        => 'nullable|string|min:3|max:100',
                'lastName'          => 'nullable|string',
                'patient_type'      => 'required|in:0,1',
                'contact_number'    => 'required|string|min:3|max:20',
                'patient_rating'    => 'required|string|in:Normal,Urgent,VIP',
                'others'            => 'nullable|string',
                'doctor_id'         => 'required|integer|exists:users,id',
                'patient_id'        => [
                    'nullable', // Allow patient_id to be null
                    'integer',
                    'exists:patient_details,id', // Adjust table/column if needed
                ],
            ], [
                // Custom error message for patient_id
                'patient_id.required' => 'The patient_id field is required when patient is exist.',
            ]);
    
            // Custom validation for patient_id when patient_type is 0
            $validator->sometimes('patient_id', 'required', function ($input) {
                // Ensure strict comparison for patient_type
                return $input->patient_type === 0 || $input->patient_type === '0';
            });
    
            if ($validator->fails()) {
                Log::debug("Validation Errors: ", $validator->errors()->toArray());
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 0) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            $new_patient = WaitingList::create([
                'firstname' => $request->firstname,
                'middleName' => $request->middleName,
                'lastName' => $request->lastName,
                'hospital_detail_id' => $request->hospital_detail_id,
                'patient_type' => $request->patient_type,
                'contact_number' => $request->contact_number,
                'patient_rating' => $request->patient_rating,
                'others' => $request->others,
                'doctor_id' => $request->doctor_id,
                'patient_id' => $request->patient_id, // Store patient_id (null if not provided)
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            if ($new_patient->id) {
                return $this->sendResponse(1, 200, 'Created successfully', 'patient_id', $new_patient->id);
            } else {
                return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.');
            }
        } catch (\Exception $e) {
            Log::debug("API AddWaitingList Error: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function ViewWaitingList(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'doctor_id'         => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $doctor_id = $request->doctor_id;
     
            $patient = ViewWaitingList::SELECT('waitingList_id','patient_id', 'firstname','middleName','lastName', 'patient_type', 'contact_number','patient_rating','doctor_name','others','doctor_id')
            ->where(['doctor_id' => $doctor_id])
            ->get();
            
            if($patient->count() != 0) {
                return $this->sendResponse(1,200, 'Success', 'data', $patient);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $patient->count());
            }
        } catch(\Exception $e) {
            Log::debug('API ViewWaitingList :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function DeleteWaitingList(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'patient_id' => 'required|integer|exists:waiting_lists,id,is_active,1'
            ]);
    
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 0) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            $waitingList = WaitingList::find($request->patient_id);
    
            if (!$waitingList) {
                return $this->sendResponse(0, 200, 'Waiting list record not found.', '');
            }
    
            $waitingList->update([
                'is_active' => 0,
                'updated_by' => $request->user_id,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    
            return $this->sendResponse(1, 200, 'Waiting list record deactivated successfully', 'patient_id', $request->patient_id);
    
        } catch (\Exception $e) {
            Log::debug("API DeleteWaitingList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdatePhoneEnquiryStatus(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'appointment_id'    => 'required|integer',
                'color_code'        => 'required|string|min:6|max:20',
                'comments'          => 'nullable|string',
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
            $exist_app = PhoneEnquiry::where(['id'=>$app_id, 'is_active' => 1])->get();
            if($exist_app->count() == 0) {
                return $this->sendResponse(0, 200, 'Record not found. Try again.');
            } 
            
            $appointment = PhoneEnquiry::find($app_id);
            $appointment->appointment_status_id = $request->appointment_status_id;
            $appointment->color_code = $request->color_code;
            $appointment->updated_by = $request->user_id;
            $appointment->checkOut = $request->checkOut;
            if(isset($request->comments) && $request->comments != ''){
                $appointment->comments   = $request->comments;
            }
            $appointment->updated_at = date('Y-m-d H:i:s');
            $appointment->update();

            if($appointment->id) {
                return $this->sendResponse(1,200, 'Status updated successfully');
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }

        } catch(\Exception $e) {
            Log::debug('API UpatePhoneEnquiryStatus :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
        
    public function DeletePhoneEnquiry(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'id' => 'required|integer|exists:phone_enquiries,id,is_active,1'
            ]);
    
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 0) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            $patient = PhoneEnquiry::find($request->id);
    
            if (!$patient) {
                return $this->sendResponse(0, 200, 'Phone enquiry record not found.', '');
            }
    
            $patient->update([
                'is_active' => 0,
                'updated_by' => $request->user_id,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    
            return $this->sendResponse(1, 200, 'Phone enquiry record deactivated successfully', 'id', $request->id);
    
        } catch (\Exception $e) {
            Log::debug("API DeletePhoneEnquiry:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetPhoneByAppointment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'phone_number'      => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $phone_number = $request->phone_number;
     
            $patient = ViewPhoneEnquiry::SELECT('id', 'patient_name', 'doctor_name', 'appointment_date','from_time','to_time','booked_by','staff_name', 'status_name', 'group_id', 'created_at')
            ->where(['primary_number' => $phone_number])
            ->limit(10)
            ->orderBy('id', 'desc')
            ->get();
            
            if($patient->count() != 0) {
                return $this->sendResponse(1,200, 'Success', 'data', $patient);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $patient->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetPhoneByAppointmnet :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

}