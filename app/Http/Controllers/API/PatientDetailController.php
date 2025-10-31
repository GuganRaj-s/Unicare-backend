<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\PatientDetail;
use App\PatientEmirate;
use App\PatientAddress;
use App\PatientDemoGraphic;
use App\PatientGuardian;
use App\PatientKin;
use App\PatientReferral;
use App\PatientOther;
use App\PatientInsurance;
use App\PatientInsuranceDetail;
use App\PhoneEnquiry;
use App\ViewPatientDetail;
use App\ViewPatientInsurance;
use App\ViewPatientInsuranceDetail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use \Validator;
use \stdClass;
use DateTime;

class PatientDetailController extends BaseController {
    public function ViewPatientList(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'hospital_detail_id' => 'required|integer',
                'search_field'      => 'required|string|in:first_name,middle_name,last_name,mr_number,primary_contact,emirate_ids,register_date',
                'search_value'      => 'required|string|min:3|max:30'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if ($this->VerifyAuthUser($request->user_id, 0) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            $condition = [];
            if ($request->hospital_detail_id != 0) {
                $hospital_detail_id = $request->hospital_detail_id;
                $condition[] = "v.hospital_detail_id = $hospital_detail_id";
            }
            $search_field = $request->search_field;
            $search_value = $request->search_value;
            if ($search_field == 'register_date') {
                $register_date = date('Y-m-d', strtotime($search_value));
                $condition[] = "DATE(v.register_date) = '$register_date'";
            } else {
                $condition[] = "v.$search_field LIKE '%$search_value%'";
            }
            
            // Combine conditions with AND
            $whereClause = !empty($condition) ? 'WHERE ' . implode(' AND ', $condition) : '';
    
            $start = 0;
            $page_limit = 1000;
            $patient = DB::select("
                SELECT 
                    v.id, 
                    DATE_FORMAT(v.register_date, '%d-%m-%Y') AS 'register_date', 
                    v.register_time, 
                    v.user_created, 
                    v.center_name, 
                    v.register_no, 
                    v.mr_number, 
                    v.first_name, 
                    v.middle_name, 
                    v.last_name, 
                    v.gender_name, 
                    v.passport_no, 
                    v.payment_mode, 
                    v.profile_image, 
                    v.primary_contact_code, 
                    v.primary_contact, 
                    v.emirate_ids, 
                    DATE_FORMAT(v.expiry_date, '%d-%m-%Y') AS 'expiry_date', 
                    v.policy_number, 
                    DATE_FORMAT(v.policy_expiry_date, '%d-%m-%Y') AS 'policy_expiry_date', 
                    v.main_company_name, 
                    v.company_short_code, 
                    v.plan_name, 
                    v.patient_status, 
                    v.referral_source,
                    v.date_of_birth,
                    v.email,
                    GROUP_CONCAT(b.doctor_id) AS doctor_ids,
                    MAX(b.block_reason) AS block_reason,
                    MAX(b.block_status) AS block_status,
                    MAX(b.patient_notes) AS patient_notes,
                    MAX(b.block_status_color) AS block_status_color
                FROM view_patient_details v
                LEFT JOIN block_patient_appointments b 
                    ON v.id = b.patient_detail_id 
                    AND v.hospital_detail_id = b.hospital_detail_id
                    AND b.block_status = '1'
                $whereClause
                GROUP BY v.id, v.register_date, v.register_time, v.user_created, v.center_name, 
                         v.register_no, v.mr_number, v.first_name, v.middle_name, v.last_name, 
                         v.gender_name, v.passport_no, v.payment_mode, v.profile_image, 
                         v.primary_contact_code, v.primary_contact, v.emirate_ids, 
                         v.expiry_date, v.policy_number, v.policy_expiry_date, 
                         v.main_company_name, v.company_short_code, v.plan_name, 
                         v.patient_status, v.referral_source
                ORDER BY v.id DESC 
                LIMIT $start, $page_limit
            ");
    // $patient = DB::select("
    //             SELECT 
    //                 v.id, 
    //                 DATE_FORMAT(v.register_date, '%d-%m-%Y') AS 'register_date', 
    //                 v.register_time, 
    //                 v.user_created, 
    //                 v.center_name, 
    //                 v.register_no, 
    //                 v.mr_number, 
    //                 v.first_name, 
    //                 v.middle_name, 
    //                 v.last_name, 
    //                 v.gender_name, 
    //                 v.passport_no, 
    //                 v.payment_mode, 
    //                 v.profile_image, 
    //                 v.primary_contact_code, 
    //                 v.primary_contact, 
    //                 v.emirate_ids, 
    //                 DATE_FORMAT(v.expiry_date, '%d-%m-%Y') AS 'expiry_date', 
    //                 v.policy_number, 
    //                 DATE_FORMAT(v.policy_expiry_date, '%d-%m-%Y') AS 'policy_expiry_date', 
    //                 v.main_company_name, 
    //                 v.company_short_code, 
    //                 v.plan_name, 
    //                 v.patient_status, 
    //                 v.referral_source,
    //                 v.date_of_birth,
    //                 v.email,
    //                 GROUP_CONCAT(b.doctor_id) AS doctor_ids,
    //                 MAX(b.block_reason) AS block_reason,
    //                 MAX(b.block_status) AS block_status,
    //                 MAX(b.patient_notes) AS patient_notes,
    //                 MAX(CASE b.block_status 
    //                     WHEN 1 THEN 'Red' 
    //                     WHEN 0 THEN 'Green' 
    //                     ELSE 'Unknown' 
    //                 END) AS block_status_color
    //             FROM view_patient_details v
    //             LEFT JOIN block_patient_appointments b 
    //                 ON v.id = b.patient_detail_id 
    //                 AND v.hospital_detail_id = b.hospital_detail_id
    //                 AND b.block_status = '1'
    //             $whereClause
    //             GROUP BY v.id, v.register_date, v.register_time, v.user_created, v.center_name, 
    //                      v.register_no, v.mr_number, v.first_name, v.middle_name, v.last_name, 
    //                      v.gender_name, v.passport_no, v.payment_mode, v.profile_image, 
    //                      v.primary_contact_code, v.primary_contact, v.emirate_ids, 
    //                      v.expiry_date, v.policy_number, v.policy_expiry_date, 
    //                      v.main_company_name, v.company_short_code, v.plan_name, 
    //                      v.patient_status, v.referral_source
    //             ORDER BY v.id DESC 
    //             LIMIT $start, $page_limit
    //         ");
    
            $image_path = config('app.image_path');
            $resp = [];
            $resp['image_path'] = $image_path . 'patient/';
            $resp['patient'] = array_map(function ($item) {
                $item->doctor_ids = $item->doctor_ids ? explode(',', $item->doctor_ids) : [];
                return $item;
            }, $patient);
    
            return $this->sendResponse(1, 200, 'Success', 'data', $resp);
        } catch (\Exception $e) {
            Log::debug("API ViewPatientListWithActiveBlocks:: " . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSinglePatient(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'patient_id'        => 'required|integer|exists:patient_details,id'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            // if($this->VerifyPageAccess('patient', 'is_view') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }

            $patient = ViewPatientDetail::with(['emirate', 'sub_address', 'others', 'guardian', 'patientkin', 'referral'])
                    ->WHERE(['id' => $request->patient_id])->first();
            $image_path = config('app.image_path');
            $resp =[];
            $patient1 = [];
            if($patient->count() != 0) {
                $resp['image_path'] = $image_path.'patient/';
                $patient1 = $patient;
                $resp['patient'][] = $patient1;
                $resp['insurance'] = [];
                if($patient->payment_mode_id == 2) {
                    $insurance = ViewPatientInsurance::with(['insurance_detail'])
                            ->WHERE(['patient_detail_id' => $request->patient_id])->get();
                    $resp['insurance'] = $insurance;
                }
                return $this->sendResponse(1,200, 'Success', 'data', $resp);
            } else {
                return $this->sendResponse(0,200, 'Record not found');
            }

            

        } catch(\Exception $e) {
            Log::debug("API GetSinglePatient:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function CreatePatient(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id',
                //'register_no'       => 'nullable|string|min:0|max:30',
                'register_time'     => 'required|string|min:0|max:30',
                'register_date'     => 'required|date',
                'user_created'      => 'required|string:min:2|max:30',
                'mr_number'         => 'nullable|string|min:0|max:30',
                'patient_class_id'  => 'required|integer|exists:patient_classes,id',
                'referral_source_id'    => 'nullable|integer|exists:referral_sources,id',
                'member_type'       => 'required|string|in:Demo Patient,Family Member,Normal',
                'payment_mode_id'   => 'required|integer|in:1,2',
                'title_id'          => 'required|integer|exists:titles,id',
                'first_name'        => 'required|string|min:3|max:30',
                'middle_name'       => 'nullable|string|min:1|max:30',
                'last_name'         => 'required|string|min:1|max:30',
                'gender_id'         => 'nullable|integer|exists:genders,id',
                'marital_status_id' => 'nullable|integer|exists:marital_statuses,id',
                'nationality_id'    => 'nullable|integer|exists:nationalities,id',
                'passport_no'       => 'nullable|string|min:0|max:30',
                'date_of_birth'     => 'nullable|date',
                'patient_age'       => 'nullable',
                'religion_id'       => 'nullable|integer|exists:religions,id',
                'ethnic_id'         => 'nullable|integer|exists:ethnics,id',
                'occupation_id'     => 'nullable|integer|exists:occupations,id',
                'education_id'      => 'nullable|integer|exists:education,id',
                'industry_id'       => 'nullable|integer|exists:industries,id',
                'income_range_id'   => 'nullable|integer|exists:income_ranges,id',
                'language_id'       => 'nullable',
                'patient_status'    => 'required|string|in:Alive,Deceased',
                'deceased_date'     => 'required_if:patient_status,==,Deceased|nullable|date',
                'profile_image'     => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'medical_tourism'   => 'nullable|string|in:Resident,Visitor',
                'visitor_type'      => 'nullable|string|in:Medical Tourism,Other Visit',
                //'arabic_name'       => 'nullable|min:0|max:30',
                //'blood_group_id'    => 'nullable|integer|exists:blood_groups,id', 
                //'father_name'       => 'nullable|min:3|max:16',
                //'primary_email'     => 'nullable|email|min:0|max:35',
                //'primary_mobile'    => 'nullable|min:7|max:15',
                //'secondary_mobile'  => 'nullable|min:0|max:15',
                //'address'           => 'nullable|string|min:3|max:250',
                //'secondary_email'   => 'nullable|email|min:0|max:35',
                //'country_id'        => 'required|integer|exists:countries,id',
                //'city_id'           => 'required|integer|exists:cities,id',
                //'region_id'         => 'required|integer|exists:regions,id', 
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
            $destinationPath = public_path('/patient');
            $profile_image_name = 'default.png';
            if($request->hasFile('profile_image'))
            {
                $profile_image = $request->file('profile_image');
                $extension = $request->file('profile_image')->extension();
                $profile_image_name = time().str_replace(' ', '_',$request->first_name).'.'.$extension;                    
                $profile_image->move($destinationPath, $profile_image_name);
            }
            $dob = null;
            $age = null;
            $register_date = null;
            if($request->date_of_birth != ''){
                $dob = date("Y-m-d", strtotime($request->date_of_birth)); 
            }

            if($request->register_date != ''){
                $register_date = date("Y-m-d", strtotime($request->register_date)); 
            }
            
            $deceased_date = null;
            if($request->deceased_date != '') {
                $deceased_date = date("Y-m-d", strtotime($request->deceased_date));
            }

            if($request->mr_number != ''){
                $is_exist_mr_number = PatientDetail::where(['mr_number'=>$request->mr_number, 'is_active' => 1])->get();
                if($is_exist_mr_number->count() != 0 ) {
                    return $this->sendResponse(0, 200, 'MR Number already exist'); 
                } 
            }
            $full_name = $request->first_name.' '.$request->middle_name.' '.$request->last_name;
            $new_patient = PatientDetail::create([
                'hospital_detail_id' => $request->hospital_detail_id,
                'patient_class_id' => $request->patient_class_id,
                'register_time' => $request->register_time,
                'register_date' => $register_date,
                'mr_number' => $request->mr_number,
                'user_created' => $request->user_created,
                'member_type' => $request->member_type,
                'payment_mode_id' => $request->payment_mode_id,
                'title_id' => $request->title_id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'full_name' => $full_name,
                'referral_source_id' => $request->referral_source_id,
                //'arabic_name' => $request->arabic_name,
                'gender_id' => $request->gender_id,
                //'blood_group_id' => $request->blood_group_id,
                //'father_name' => $request->father_name,
                'nationality_id' => $request->nationality_id,
                'passport_no' => $request->passport_no,
                'marital_status_id' => $request->marital_status_id,
                'occupation_id' => $request->occupation_id,
                'education_id' => $request->education_id,
                'religion_id' => $request->religion_id,
                //'primary_email' => $request->primary_email,
                'ethnic_id' => $request->ethnic_id,
                'language_id' => $request->language_id,
                'date_of_birth' => $dob,
                'patient_age' => $request->patient_age,
                //'primary_mobile' => $request->primary_mobile,
                //'secondary_mobile' => $request->secondary_mobile,
                'industry_id' => $request->industry_id,
                'income_range_id' => $request->income_range_id,
                'patient_status' => $request->patient_status,
                'deceased_date' => $deceased_date,
                'profile_image' => $profile_image_name,
                'medical_tourism' => $request->medical_tourism,
                'visitor_type' => $request->visitor_type,
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
            Log::debug("API CreatePatient:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function UpdatePatient(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'patient_id'        => 'required|integer|exists:patient_details,id',
                'user_id'           => 'required|integer|exists:users,id',
                'hospital_detail_id'=> 'required|integer|exists:hospital_details,id',
                //'register_no'       => 'required|integer',
                'register_time'     => 'required|string|min:0|max:30',
                'register_date'     => 'required|date',
                'user_created'      => 'required|string:min:2|max:30',
                'mr_number'         => 'nullable|string|min:0|max:30',
                'referral_source_id'    => 'nullable|integer|exists:referral_sources,id',
                'patient_class_id'  => 'required|integer|exists:patient_classes,id',
                'member_type'       => 'required|string|in:Demo Patient,Family Member,Normal',
                'payment_mode_id'   => 'required|integer|in:1,2',
                'title_id'          => 'required|integer|exists:titles,id',
                'first_name'        => 'required|string|min:3|max:30',
                'middle_name'       => 'nullable|string|min:1|max:30',
                'last_name'         => 'required|string|min:1|max:30',
                'gender_id'         => 'nullable|integer|exists:genders,id',
                'marital_status_id' => 'nullable|integer|exists:marital_statuses,id',
                'nationality_id'    => 'nullable|integer|exists:nationalities,id',
                'passport_no'       => 'nullable|string|min:0|max:30',
                'date_of_birth'     => 'nullable|date',
                'patient_age'       => 'nullable',
                'religion_id'       => 'nullable|integer|exists:religions,id',
                'ethnic_id'         => 'nullable|integer|exists:ethnics,id',
                'occupation_id'     => 'nullable|integer|exists:occupations,id',
                'education_id'      => 'nullable|integer|exists:education,id',
                'industry_id'       => 'nullable|integer|exists:industries,id',
                'income_range_id'   => 'nullable|integer|exists:income_ranges,id',
                'language_id'       => 'nullable',
                'patient_status'    => 'required|string|in:Alive,Deceased',
                'deceased_date'     => 'required_if:patient_status,==,Deceased|nullable|date',
                'profile_image'     => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'medical_tourism'   => 'nullable|string|in:Resident,Visitor',
                'visitor_type'      => 'nullable|string|in:Medical Tourism,Other Visit'
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
            if($request->mr_number != ''){
                $is_exist_mr_number = PatientDetail::where(['mr_number'=>$request->mr_number, 'is_active' => 1])
                    ->where('id', '!=' , $request->patient_id)->get();
                if($is_exist_mr_number->count() != 0 ) {
                    return $this->sendResponse(0, 200, 'MR Number already exist'); 
                }
            }
            
            $destinationPath = public_path('/patient');
            $profile_image_name = '';
            if($request->hasFile('profile_image'))
            {
                $profile_image = $request->file('profile_image');
                $extension = $request->file('profile_image')->extension();
                $profile_image_name = time().str_replace(' ', '_',$request->first_name).'.'.$extension;                    
                $profile_image->move($destinationPath, $profile_image_name);
            }
            $dob = null;
            $age = null;
            $register_date = null;
            if($request->date_of_birth != ''){
                $dob = date("Y-m-d", strtotime($request->date_of_birth)); 
            }

            if($request->register_date != ''){
                $register_date = date("Y-m-d", strtotime($request->register_date)); 
            }

            $deceased_date = null;
            if(isset($request->deceased_date) && $request->deceased_date != '') {
                $deceased_date = date("Y-m-d", strtotime($request->deceased_date));
            }

            $deceased_date = null;
            if(isset($request->deceased_date) && $request->deceased_date != '') {
                $deceased_date = date("Y-m-d", strtotime($request->deceased_date));
            }
            $full_name = $request->first_name.' '.$request->middle_name.' '.$request->last_name;
            $patient = PatientDetail::find($request->patient_id);
            $patient->title_id = $request->title_id;
            $patient->register_no = $request->patient_id;
            $patient->register_time = $request->register_time;
            $patient->register_date = $register_date;
            $patient->hospital_detail_id = $request->hospital_detail_id;
            $patient->mr_number = $request->mr_number;
            $patient->patient_class_id = $request->patient_class_id;
            $patient->first_name = $request->first_name;
            $patient->middle_name = $request->middle_name;
            $patient->last_name = $request->last_name;
            $patient->full_name = $full_name;
            $patient->date_of_birth = $dob;
            $patient->patient_age = $request->patient_age;
            $patient->ethnic_id = $request->ethnic_id;
            $patient->education_id = $request->education_id;
            $patient->occupation_id = $request->occupation_id;
            $patient->member_type = $request->member_type;
            $patient->gender_id = $request->gender_id;
            //$patient->father_name = $request->father_name;
            $patient->referral_source_id = $request->referral_source_id;
            $patient->marital_status_id = $request->marital_status_id;
            $patient->religion_id = $request->religion_id;
            $patient->nationality_id = $request->nationality_id;
            //$patient->blood_group_id = $request->blood_group_id;
            $patient->language_id = $request->language_id;
            $patient->passport_no = $request->passport_no;
            //$patient->primary_mobile = $request->primary_mobile;
            //$patient->secondary_mobile = $request->secondary_mobile;
            $patient->user_created = $request->user_created;
            $patient->industry_id = $request->industry_id;
            $patient->income_range_id = $request->income_range_id;
            $patient->patient_status = $request->patient_status;
            $patient->deceased_date = $deceased_date;
            $patient->medical_tourism = $request->medical_tourism;
            $patient->visitor_type = $request->visitor_type;
            $patient->payment_mode_id = $request->payment_mode_id;
            if($profile_image_name != ''){
                $patient->profile_image = $profile_image_name;
            }
            $patient->updated_by = $request->user_id;
            $patient->updated_at = date('Y-m-d H:i:s');
            $patient->update();

            if($patient) {
                return $this->sendResponse(1,200, 'Patient details updated successfully', 'patient_id', $patient->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdatePatient:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function CheckEmirateId(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'patient_detail_id' => 'required|integer',
                'emirate_ids'       => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $patient_detail_id = $request->patient_detail_id;
            $emirate_ids = $request->emirate_ids;


            if($patient_detail_id == 0) {
                $is_exist = PatientEmirate::where(['emirate_ids' => $emirate_ids, 'is_active' => 1])->count();
            } else {
                $emirat = PatientEmirate::SELECT('id', 'patient_detail_id')
                    ->where(['patient_detail_id' => $patient_detail_id, 'is_active' => 1])->get();
                $emi_id = $emirat[0]->id;
                $is_exist = PatientEmirate::where(['patient_detail_id' => $patient_detail_id, 'is_active' => 1])
                    ->where('id', '!=' , $emi_id)->count();
            }
            if($is_exist != 0) {
                return $this->sendResponse(0, 200, 'Emirates ID already exist.');
            } else {
                return $this->sendResponse(1, 200, 'Success');
            }
                
            

        } catch(\Exception $e) {
            Log::debug("API CheckEmirateId:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    // public function SavePatientSubdData(Request $request) {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'user_id' => 'required|integer|exists:users,id',
    //             'patient_id' => 'required|integer|exists:patient_details,id',
    //         ]);
    //         if ($validator->fails()) {
    //             return $this->sendResponse(0, 200, $validator->errors()->first(), '');
    //         }
    //         if ($this->VerifyAuthUser($request->user_id, 0) === false) {
    //             return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
    //         }
    
    //         $patient_detail_id = $request->patient_id;
    //         $save_emirate = '';
    
    //         if (!empty($request->emirate)) {
    //             $destinationPath = public_path('/patient');
    //             $emirate_ids = $request->emirate['emirate_ids'];
    
    //             $validator2 = Validator::make($request->all(), [
    //                 'emirate.front_page' => 'nullable|mimes:jpeg,jpg,png|max:5120',
    //             ]);
    //             if ($validator2->fails()) {
    //                 $front_page_name = '';
    //             } else {
    //                 if (isset($request->emirate['front_page']) && !empty($request->emirate['front_page'])) {
    //                     $front_page = $request->emirate['front_page'];
    //                     $extension = $request->emirate['front_page']->extension();
    //                     $front_page_name = time() . str_replace(' ', '_', '_front_' . $emirate_ids) . '.' . $extension;
    //                     $front_page->move($destinationPath, $front_page_name);
    //                 } else {
    //                     $front_page_name = '';
    //                 }
    //             }
    
    //             $validator3 = Validator::make($request->all(), [
    //                 'emirate.back_page' => 'nullable|mimes:jpeg,jpg,png|max:5120',
    //             ]);
    //             if ($validator3->fails()) {
    //                 $back_page_name = '';
    //             } else {
    //                 if (isset($request->emirate['back_page']) && !empty($request->emirate['back_page'])) {
    //                     $back_page = $request->emirate['back_page'];
    //                     $extension = $request->emirate['back_page']->extension();
    //                     $back_page_name = time() . str_replace(' ', '_', '_back_' . $emirate_ids) . '.' . $extension;
    //                     $back_page->move($destinationPath, $back_page_name);
    //                 } else {
    //                     $back_page_name = '';
    //                 }
    //             }
    
    //             $save_emirate = $this->SaveEmirates($request->emirate, $patient_detail_id, $request->user_id, $front_page_name, $back_page_name);
    //             // Check for error response from SaveEmirates
    //             if (is_array($save_emirate) && isset($save_emirate['status']) && $save_emirate['status'] == 0) {
    //                 return $save_emirate;
    //             }
    //         }
    
    //         if (!empty($request->patient_address)) {
    //             $save_address = $this->SaveAddress($request->patient_address, $patient_detail_id, $request->user_id);
    //             // Check for error response from SaveAddress
    //             if (is_array($save_address) && isset($save_address['status']) && $save_address['status'] == 0) {
    //                 return $save_address;
    //             }
    //         }
    
    //         if (!empty($request->guardian)) {
    //             $save_guardian = $this->SaveGuardian($request->guardian, $patient_detail_id, $request->user_id);
    //             // Check for error response from SaveGuardian
    //             if (is_array($save_guardian) && isset($save_guardian['status']) && $save_guardian['status'] == 0) {
    //                 return $save_guardian;
    //             }
    //         }
    
    //         if (!empty($request->nextkin)) {
    //             $save_nextkin = $this->SaveNextOfKin($request->nextkin, $patient_detail_id, $request->user_id);
    //             // Check for error response from SaveNextOfKin
    //             if (is_array($save_nextkin) && isset($save_nextkin['status']) && $save_nextkin['status'] == 0) {
    //                 return $save_nextkin;
    //             }
    //         }
    
    //         if (!empty($request->others)) {
    //             $save_others = $this->SaveOthers($request->others, $patient_detail_id, $request->user_id);
    //             // Check for error response from SaveOthers
    //             if (is_array($save_others) && isset($save_others['status']) && $save_others['status'] == 0) {
    //                 return $save_others;
    //             }
    //         }
    
    //         return $this->sendResponse(1, 200, 'Patient data saved successfully');
    //     } catch (\Exception $e) {
    //         Log::debug("API SavePatientSubdData:: " . $e->getMessage());
    //         return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong, try again later.');
    //     }
    // }
    
    public function SavePatientSubdData(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'patient_id'       => 'required|integer|exists:patient_details,id',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $patient_detail_id = $request->patient_id;
            $save_emirate = '';

            
            if(!empty($request->emirate)) {
                $destinationPath = public_path('/patient');
                $emirate_ids = $request->emirate['emirate_ids'];

                /*if($emirate_ids !=  ''){
                    if($emirate_ids != '999999999999999'){
                        $emirat = PatientEmirate::SELECT('id', 'patient_detail_id')
                            ->where(['patient_detail_id' => $patient_detail_id, 'is_active' => 1])->get();
                        if($emirat->count() == 0) {
                            $is_exist = PatientEmirate::where(['emirate_ids' => $emirate_ids, 'is_active' => 1])->get();
                        } else {
                            $emi_id = $emirat[0]->id;
                            $is_exist = PatientEmirate::where(['emirate_ids' => $emirate_ids, 'is_active' => 1])
                                ->where('id', '!=' , $emi_id)->get();
                        }
                        if($is_exist->count() != 0) {
                            return $this->sendResponse(0, 200, 'Emirates ID already exist.');
                        }
                    }
                }*/

                $validator2 = Validator::make($request->all(), [
                    'emirate.front_page'  => 'nullable|mimes:jpeg,jpg,png|max:5120',
                ]);
                if ($validator2->fails()) {
                    $front_page_name = '';
                } else {
                    if(isset($request->emirate['front_page']) && !empty($request->emirate['front_page'])){
                        $front_page = $request->emirate['front_page'];
                        $extension = $request->emirate['front_page']->extension();
                        $front_page_name = time().str_replace(' ', '_','_front_'.$emirate_ids).'.'.$extension;                    
                        $front_page->move($destinationPath, $front_page_name);
                    } else {
                        $front_page_name = '';
                    }
                }

                $validator3 = Validator::make($request->all(), [
                    'emirate.back_page'   => 'nullable|mimes:jpeg,jpg,png|max:5120',
                ]);

                if ($validator3->fails()) {
                    $back_page_name = '';
                } else {
                    if(isset($request->emirate['back_page']) && !empty($request->emirate['back_page'])){
                        $back_page = $request->emirate['back_page'];
                        $extension = $request->emirate['back_page']->extension();
                        $back_page_name = time().str_replace(' ', '_','_back_'.$emirate_ids).'.'.$extension;                    
                        $back_page->move($destinationPath, $back_page_name);
                    } else {
                        $back_page_name = '';
                    }
                }
                    
                $save_emirate = $this->SaveEmirates($request->emirate, $patient_detail_id, $request->user_id, $front_page_name, $back_page_name);
            }

            if(!empty($request->patient_address)) { 
                $save_address = $this->SaveAddress($request->patient_address, $patient_detail_id, $request->user_id);
            }

            // if(!empty($request->demographic)) { 
            //     $save_address = $this->SaveDemoGraphics($request->demographic, $patient_detail_id, $request->user_id);
            // }

            if(!empty($request->guardian)) {
                 $save_guardian = $this->SaveGuardian($request->guardian, $patient_detail_id, $request->user_id);
            }

            if(!empty($request->nextkin)) {
                $save_nextkin = $this->SaveNextOfKin($request->nextkin, $patient_detail_id, $request->user_id);
            }

            if(!empty($request->others)) {
                $save_others = $this->SaveOthers($request->others, $patient_detail_id, $request->user_id);
            }

            return $this->sendResponse(1,200, 'Success');

        } catch(\Exception $e) {
            Log::debug("API SavePatientSubdData:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }
    }
    
    public function SaveAddress($address_data, $patient_detail_id, $user_id) {
        try {
            // Validate address_data
            $validator = Validator::make($address_data, [
                'address' => 'required|string',
                'country_id' => 'required|integer',
                'region_id' => 'required|integer',
                'city_id' => 'required|integer',
                'primary_contact' => 'required|string',
                'email' => 'nullable|email'
            ]);
    
            if ($validator->fails()) {
                Log::debug("SaveAddress validation failed: " . $validator->errors()->first());
                return $this->sendResponse(0, 200, $validator->errors()->first(), 'error', 'Address validation failed.');
            }
    
            $address = PatientAddress::select('id', 'patient_detail_id')
                ->where(['patient_detail_id' => $patient_detail_id, 'is_active' => 1])
                ->get();
            $address_id = '';
    
            // Combine and normalize primary_contact_code and primary_contact
            $primary_phone = '+' . (isset($address_data['primary_contact_code']) ? $address_data['primary_contact_code'] : '') . $address_data['primary_contact'];
            $normalized_phone = preg_replace('/\D/', '', $primary_phone); // Remove non-digits (spaces, dashes, etc.)
            $normalized_phone = '+' . $normalized_phone; // Ensure + prefix
            Log::debug("SaveAddress: patient_detail_id = $patient_detail_id, user_id = $user_id, primary_phone = $primary_phone, normalized_phone = $normalized_phone");
    
            // Log all phone numbers in PhoneEnquiry for debugging
            $phoneRecords = PhoneEnquiry::select('primary_number', 'secondary_number', 'whatsup_number')->get();
            Log::debug("SaveAddress: PhoneEnquiry records = " . json_encode($phoneRecords));
    
            if ($address->count() == 0) {
                $PatientAddress = PatientAddress::create([
                    'patient_detail_id' => $patient_detail_id,
                    'address' => $address_data['address'],
                    'country_id' => $address_data['country_id'],
                    'region_id' => $address_data['region_id'],
                    'city_id' => $address_data['city_id'],
                    'post_box_no' => $address_data['post_box_no'] ?? '',
                    'home_telephone' => $address_data['home_telephone'] ?? '',
                    'work_telephone' => $address_data['work_telephone'] ?? '',
                    'whatsup_number' => $address_data['whatsup_number'] ?? 1,
                    'no_email_available' => $address_data['no_email_available'] ?? 0,
                    'primary_contact_code' => $address_data['primary_contact_code'] ?? '',
                    'secondary_contact_code' => $address_data['secondary_contact_code'] ?? '',
                    'primary_contact' => $address_data['primary_contact'],
                    'secondary_contact' => $address_data['secondary_contact'] ?? '',
                    'email' => $address_data['email'] ?? '',
                    'created_by' => $user_id,
                    'updated_by' => $user_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $address_id = $PatientAddress->id;
    
                Log::debug("SaveAddress: Add address with ID = $address_id");
    
                // Update PhoneEnquiry with normalized comparison
                if ($normalized_phone) {
                    $matchingRecords = PhoneEnquiry::whereRaw("REPLACE(primary_number, ' ', '') = ?", [$normalized_phone])
                        ->orWhereRaw("REPLACE(secondary_number, ' ', '') = ?", [$normalized_phone])
                        ->orWhereRaw("REPLACE(whatsup_number, ' ', '') = ?", [$normalized_phone])
                        ->count();
                    Log::debug("SaveAddress: Found $matchingRecords matching records in PhoneEnquiry for normalized_phone = $normalized_phone");
    
                    $affectedRows = PhoneEnquiry::whereRaw("REPLACE(primary_number, ' ', '') = ?", [$normalized_phone])
                        ->orWhereRaw("REPLACE(secondary_number, ' ', '') = ?", [$normalized_phone])
                        ->orWhereRaw("REPLACE(whatsup_number, ' ', '') = ?", [$normalized_phone])
                        ->update(['patient_id' => $patient_detail_id]);
                    Log::debug("SaveAddress: PhoneEnquiry update attempted, affected rows = $affectedRows");
    
                    if ($matchingRecords > 0 && $affectedRows == 0) {
                        Log::debug("SaveAddress: Failed to update PhoneEnquiry despite matching records");
                        return $this->sendResponse(0, 200, 'Failed to update PhoneEnquiry', 'error', 'No records updated in PhoneEnquiry.');
                    }
                } else {
                    Log::debug("SaveAddress: normalized_phone is empty, skipping PhoneEnquiry update");
                }
            } else {
                $address_id = $address[0]->id;
                $PatientAddress = PatientAddress::find($address_id);
                $PatientAddress->address = $address_data['address'];
                $PatientAddress->country_id = $address_data['country_id'];
                $PatientAddress->region_id = $address_data['region_id'];
                $PatientAddress->city_id = $address_data['city_id'];
                $PatientAddress->post_box_no = $address_data['post_box_no'] ?? '';
                $PatientAddress->home_telephone = $address_data['home_telephone'] ?? '';
                $PatientAddress->work_telephone = $address_data['work_telephone'] ?? '';
                $PatientAddress->primary_contact_code = $address_data['primary_contact_code'] ?? '';
                $PatientAddress->secondary_contact_code = $address_data['secondary_contact_code'] ?? '';
                $PatientAddress->primary_contact = $address_data['primary_contact'];
                $PatientAddress->secondary_contact = $address_data['secondary_contact'] ?? '';
                $PatientAddress->email = $address_data['email'] ?? '';
                $PatientAddress->updated_by = $user_id;
                $PatientAddress->updated_at = now();
                $PatientAddress->update();
                Log::debug("SaveAddress: Updated address with ID = $address_id");
    
                // Update PhoneEnquiry with normalized comparison
                if ($normalized_phone) {
                    $matchingRecords = PhoneEnquiry::whereRaw("REPLACE(primary_number, ' ', '') = ?", [$normalized_phone])
                        ->orWhereRaw("REPLACE(secondary_number, ' ', '') = ?", [$normalized_phone])
                        ->orWhereRaw("REPLACE(whatsup_number, ' ', '') = ?", [$normalized_phone])
                        ->count();
                    Log::debug("SaveAddress: Found $matchingRecords matching records in PhoneEnquiry for normalized_phone = $normalized_phone");
    
                    $affectedRows = PhoneEnquiry::whereRaw("REPLACE(primary_number, ' ', '') = ?", [$normalized_phone])
                        ->orWhereRaw("REPLACE(secondary_number, ' ', '') = ?", [$normalized_phone])
                        ->orWhereRaw("REPLACE(whatsup_number, ' ', '') = ?", [$normalized_phone])
                        ->update(['patient_id' => $patient_detail_id]);
                    Log::debug("SaveAddress: PhoneEnquiry update attempted, affected rows = $affectedRows");
    
                    if ($matchingRecords > 0 && $affectedRows == 0) {
                        Log::debug("SaveAddress: Failed to update PhoneEnquiry despite matching records");
                        return $this->sendResponse(0, 200, 'Failed to update PhoneEnquiry', 'error', 'No records updated in PhoneEnquiry.');
                    }
                } else {
                    Log::debug("SaveAddress: normalized_phone is empty, skipping PhoneEnquiry update");
                }
            }
    
            return $this->sendResponse(1, 200, 'Address saved successfully', $address_id);
        } catch (\Exception $e) {
            Log::debug("API SaveAddress:: " . $e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong, try again later.');
        }
    }

    public function GetAge($date) {
        $from = new DateTime($date);
        $to   = new DateTime('today');
        return $from->diff($to)->y;
    }

    public function SaveEmirates($emirate_data = array(), $patient_detail_id, $user_id, $front_page_name, $back_page_name) {
        try {
            $destinationPath = public_path('/patient');
            $emirate_ids = $emirate_data['emirate_ids'];
           
            $expiry = null;
            if($emirate_data['expiry_date'] != ''){
                $expiry = date("Y-m-d", strtotime($emirate_data['expiry_date'])); 
            }
            

            $emirat = PatientEmirate::SELECT('id', 'patient_detail_id')
                    ->where(['patient_detail_id' => $patient_detail_id, 'is_active' => 1])->get();
            if($emirat->count() == 0) {
                $PatientEmirate = PatientEmirate::create([
                    'patient_detail_id' => $patient_detail_id,
                    'emirate_ids' => $emirate_data['emirate_ids'],
                    'expiry_date' => $expiry,
                    'emirate_ids_front' => $front_page_name,
                    'emirate_ids_back' => $back_page_name,
                    'created_by' => $user_id,
                    'updated_by' => $user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $emi_id = $PatientEmirate->id;
            } else {
                $emi_id = $emirat[0]->id;
                $emi = PatientEmirate::find($emi_id);
                $emi->emirate_ids = $emirate_data['emirate_ids'];
                $emi->expiry_date = $expiry;
                if($front_page_name != ''){
                    $emi->emirate_ids_front = $front_page_name;
                }
                if($back_page_name != ''){
                    $emi->emirate_ids_back = $back_page_name;
                }
                $emi->updated_by = $user_id;
                $emi->updated_at = date('Y-m-d H:i:s');
                $emi->update();
            }
            return $emi_id;
        } catch(\Exception $e) {
            Log::debug("API SaveEmirates:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }
    }

    public function SaveDemoGraphics($demographicq = array(), $patient_detail_id, $user_id) {
        try {
            $address = PatientDemoGraphic::SELECT('id', 'patient_detail_id')
                ->where(['patient_detail_id' => $patient_detail_id, 'is_active' => 1])->get();
            $address_id = '';
            if($address->count() == 0) {
                $PatientDemoGraphic = PatientDemoGraphic::create([
                    'patient_detail_id' => $patient_detail_id,
                    'ethnic_id' => $demographicq['ethnic_id'],
                    'language_id' => $demographicq['language_id'],
                    'education_id' => $demographicq['education_id'],
                    'occupation_id' => $demographicq['occupation_id'],
                    'created_by' => $user_id,
                    'updated_by' => $user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $graphic_id = $PatientDemoGraphic->id;
            } else {
                $graphic_id = $address[0]->id;
                $address_data = PatientDemoGraphic::find($graphic_id);
                $address_data->ethnic_id        = $demographicq['ethnic_id'];
                $address_data->language_id      = $demographicq['language_id'];
                $address_data->education_id     = $demographicq['education_id'];
                $address_data->occupation_id    = $demographicq['occupation_id'];
                $address_data->updated_by       = $user_id;
                $address_data->updated_at       = date('Y-m-d H:i:s');
                $address_data->update();
            }
            return $graphic_id;
        } catch(\Exception $e) {
            Log::debug("API SaveDemoGraphics:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }
    }

    public function SaveGuardian($guardian = array(), $patient_detail_id, $user_id) {
        try {

            $address = PatientGuardian::SELECT('id', 'patient_detail_id')
                ->where(['patient_detail_id' => $patient_detail_id, 'is_active' => 1])->get();
            $address_id = '';
            if($address->count() != 0) {
                $delete = DB::table('patient_guardians')->where('patient_detail_id', $patient_detail_id)->delete();
            }
            foreach($guardian as $data) {
                $middlename = '';
                if(isset($data['middlename'])) {
                    $middlename = $data['middlename'];
                }
                $PatientGuardian = PatientGuardian::create([
                    'patient_detail_id' => $patient_detail_id,
                    'relationship_id' => $data['relationship_id'],
                    'firstname' => $data['firstname'],
                    'middlename' => $middlename,
                    'lastname' => $data['lastname'],
                    'mr_number' => $data['mr_number'],
                    'mobile_no' => $data['mobile_no'],
                    'email' => $data['email'],
                    'address' => $data['address'],
                    'country_id' => $data['country_id'],
                    'region_id' => $data['region_id'],
                    'city_id' =>  $data['city_id'],
                    // 'post_box_no' => $data['post_box_no'],
                    // 'landline_number' => $data['landline_number'],
                    // 'office_phone' => $data['office_phone'],
                    'created_by' => $user_id,
                    'updated_by' => $user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            return $PatientGuardian->id;
        } catch(\Exception $e) {
            Log::debug("API SaveGuardian:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }
    }

    public function SaveNextOfKin($nextkin = array(), $patient_detail_id, $user_id) {
        try {
            $address = PatientKin::SELECT('id', 'patient_detail_id')
                ->where(['patient_detail_id' => $patient_detail_id, 'is_active' => 1])->get();
            $address_id = '';
            if($address->count() != 0) {
                $delete = DB::table('patient_kin')->where('patient_detail_id', $patient_detail_id)->delete();
            }
            foreach($nextkin as $data) {
                $middlename = '';
                if(isset($data['middlename'])) {
                    $middlename = $data['middlename'];
                }
                $PatientKin = PatientKin::create([
                    'patient_detail_id' => $patient_detail_id,
                    'relationship_id' => $data['relationship_id'],
                    'firstname' => $data['firstname'],
                    'middlename' => $middlename,
                    'lastname' => $data['lastname'],
                    'mr_number' => $data['mr_number'],
                    'mobile_no' => $data['mobile_no'],
                    'email' => $data['email'],
                    'address' => $data['address'],
                    'country_id' => $data['country_id'],
                    'region_id' => $data['region_id'],
                    'city_id' => $data['city_id'],
                    // 'post_box_no' => $data['post_box_no'],
                    // 'landline_number' => $data['landline_number'],
                    // 'office_phone' => $data['office_phone'],
                    'created_by' => $user_id,
                    'updated_by' => $user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            return $PatientKin->id;
        } catch(\Exception $e) {
            Log::debug("API SaveNextOfKin:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }
    }


    public function SaveReferral(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'               => 'required|integer|exists:users,id',
                'referral_channel_id'   => 'nullable|integer',
                'patient_detail_id'     => 'required|integer|exists:patient_details,id',
                'doctor_name'           => 'nullable|string|min:1|max:30',
                'clinic_name'           => 'nullable|string|min:1|max:30',
                'license_no'            => 'nullable|string|min:1|max:30'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
           
            $referral = PatientReferral::SELECT('id', 'patient_detail_id')
                    ->where(['patient_detail_id' => $request->patient_detail_id, 'is_active' => 1])->get();
            if($referral->count() == 0) {
                $PatientReferral = PatientReferral::create([
                    'patient_detail_id' => $request->patient_detail_id,
                    'doctor_name' => $request->doctor_name,
                    'clinic_name' => $request->clinic_name,
                    'license_no' => $request->license_no,
                    'referral_channel_id' => $request->referral_channel_id,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                if($PatientReferral)
                    return $this->sendResponse(1, 200, 'Patient referral addedd', 'patient_referral_id', $PatientReferral->id);
                else
                    return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.');
            } else {
                $referral_id = $referral[0]->id;
                $ref = PatientReferral::find($referral_id);
                $ref->patient_detail_id = $request->patient_detail_id;
                $ref->doctor_name = $request->doctor_name;
                $ref->clinic_name = $request->clinic_name;
                $ref->license_no = $request->license_no;
                $ref->referral_channel_id = $request->referral_channel_id;
                $ref->updated_by = $request->user_id;
                $ref->updated_at = date('Y-m-d H:i:s');
                $ref->update();
                if($ref) {
                    return $this->sendResponse(1, 200, 'Patient referral updated', 'patient_referral_id', $ref->id);
                } else {
                    return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.');
                }
            }
            
        } catch(\Exception $e) {
            Log::debug("API SaveReferral:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }
    }

    public function SaveOthers($other_data, $patient_detail_id, $user_id) {
        try {
           
            $others = PatientOther::SELECT('id', 'patient_detail_id')
                    ->where(['patient_detail_id' => $patient_detail_id, 'is_active' => 1])->get();
            if($others->count() == 0) {
                $PatientOther = PatientOther::create([
                    'patient_detail_id' => $patient_detail_id,
                    'unified_no' => $other_data['unified_no'],
                    'mothers_eid' => $other_data['mothers_eid'],
                    'multiple_birth' => $other_data['multiple_birth'],
                    'birth_order' => $other_data['birth_order'],
                    'birth_place' => $other_data['birth_place'],
                    'created_by' => $user_id,
                    'updated_by' => $user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                if($PatientOther)
                    return $this->sendResponse(1, 200, 'Patient other detail saved.', 'patient_other_id', $PatientOther->id);
                else
                    return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.');
            } else {
                $others_id = $others[0]->id;
                $ref = PatientOther::find($others_id);
                //$ref->patient_detail_id = $patient_detail_id;
                $ref->unified_no = $other_data['unified_no'];
                $ref->mothers_eid = $other_data['mothers_eid'];
                $ref->multiple_birth = $other_data['multiple_birth'];
                $ref->birth_order = $other_data['birth_order'];
                $ref->birth_place = $other_data['birth_place'];
                $ref->updated_by = $user_id;
                $ref->updated_at = date('Y-m-d H:i:s');
                $ref->update();
                if($ref) {
                    return $this->sendResponse(1, 200, 'Patient other detail updated.', 'patient_other_id', $ref->id);
                } else {
                    return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.');
                }
            }
            
        } catch(\Exception $e) {
            Log::debug("API SaveOthers:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }
    }

    public function SavePatientInsurance(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'                   => 'required|integer|exists:users,id',
                'patient_detail_id'         => 'required|integer|exists:patient_details,id',
                'patient_insurance_id'      => 'required|integer',
                'insurance_company_detail_id'=> 'required|integer|exists:insurance_company_details,id',
                'sub_company_detail_id'     => 'nullable|integer|exists:insurance_company_details,id',
                'insurance_package_id'      => 'nullable|integer|exists:insurance_packages,id',
                'insurance_network_id'      => 'required|integer|exists:insurance_networks,id',
                'insurance_plan_id'         => 'required|integer|exists:insurance_plans,id',
                'company_type_id'           => 'nullable|integer|exists:company_types,id',
                'card_number'               => 'required|min:5|max:30',
                'policy_holder'             => 'nullable|string|min:3|max:30',
                'expiry_date'               => 'required|date',
                'max_ceilling'              => 'nullable',
                'deduct_amount'             => 'nullable',
                'co_pay_amount'             => 'nullable',
                'co_pay_all_service'        => 'required|integer|in:1,0',
                'is_status'                 => 'required|integer|in:1,0',
                'with_consultation'         => 'required|integer|in:1,0'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            $categories = $request->categories;
            if(empty($categories)) {
                return $this->sendResponse(0,200, 'Service category detail is required');
            }

            $expiry_date = null;
            if($request->expiry_date!= ''){
                $expiry_date = date("Y-m-d", strtotime($request->expiry_date)); 
            }
            $patient_detail_id = $request->patient_detail_id;
            $patient_insurance_id = $request->patient_insurance_id;
            if($patient_insurance_id == 0) {
                $is_exist_card_number = PatientInsurance::where(['card_number'=>$request->card_number, 'is_active' => 1])->get();
                if($is_exist_card_number->count() != 0 ) {
                    return $this->sendResponse(0, 200, 'Insurance Card Number already exist'); 
                }
            } else if($patient_insurance_id != 0) {
                $is_exist_card_number = PatientInsurance::SELECT('id', 'patient_detail_id')
                    ->where(['patient_detail_id' => $patient_detail_id, 'card_number'=>$request->card_number, 'is_active' => 1])
                    ->where('id', '!=' , $patient_insurance_id)->get();
                if($is_exist_card_number->count() != 0 ) {
                    return $this->sendResponse(0, 200, 'Insurance Card Number already exist'); 
                }
            }
            $patient = PatientInsurance::SELECT('id', 'patient_detail_id')
                        ->where(['patient_detail_id' => $patient_detail_id, 'is_active' => 1])->get();
            
            if(isset($request->delete_ids) && !empty($request->delete_ids)) {
                $update = PatientInsuranceDetail::where(['patient_detail_id' => $patient_detail_id])
                    ->WHEREIN('id', $request->delete_ids)
                    ->update([
                        'is_active' => 0,
                    ]); 
            }    
            if($patient_insurance_id == 0) {
                $PatientInsurance = PatientInsurance::create([
                    'patient_detail_id' => $request->patient_detail_id,
                    'insurance_company_detail_id' =>$request->insurance_company_detail_id,
                    'sub_company_detail_id' => $request->sub_company_detail_id,
                    'insurance_package_id' => $request->insurance_package_id,
                    'insurance_network_id' => $request->insurance_network_id,
                    'insurance_plan_id' => $request->insurance_plan_id,
                    'company_type_id' => $request->company_type_id,
                    'policy_holder' => $request->policy_holder,
                    'card_number' => $request->card_number,
                    'expiry_date' =>$expiry_date,
                    'max_ceilling' => $request->max_ceilling,
                    'deduct_amount' => $request->deduct_amount,
                    'co_pay_amount' => $request->co_pay_amount,
                    'co_pay_all_service' => $request->co_pay_all_service,
                    'is_status' => $request->is_status,
                    'with_consultation' => $request->with_consultation,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                if($PatientInsurance) {
                    foreach($categories as $desc) {
                        $detail = PatientInsuranceDetail::create([
                            'patient_detail_id' => $request->patient_detail_id,
                            'patient_insurance_id' => $PatientInsurance->id,
                            'department_id' => $desc['department_id'],
                            'co_pay_percentage' => $desc['co_pay_percentage'],
                            'deductible_amount' => $desc['deductible_amount'],
                            'per_invoice' => $desc['per_invoice'],
                            'max_ceilling' => $desc['max_ceilling'],
                            'created_by' => $request->user_id,
                            'updated_by' => $request->user_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                    return $this->sendResponse(1, 200, 'Patient Insurance detail saved.', 'patient_insurance_id', $PatientInsurance->id);
                } else {
                    return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.');
                }
            } else {
                $exist_ins = PatientInsurance::where(['id'=>$patient_insurance_id, 'patient_detail_id' => $patient_detail_id, 'is_active' => 1])->get();
                if($exist_ins->count() == 0) {
                    return $this->sendResponse(0, 200, 'Record not found. Try again.');
                } 
                $insurance = PatientInsurance::find($patient_insurance_id);
                $insurance->insurance_company_detail_id =$request->insurance_company_detail_id;
                $insurance->sub_company_detail_id   = $request->sub_company_detail_id;
                $insurance->insurance_package_id    = $request->insurance_package_id;
                $insurance->insurance_network_id    = $request->insurance_network_id;
                $insurance->insurance_plan_id       = $request->insurance_plan_id;
                $insurance->policy_holder           = $request->policy_holder;
                $insurance->company_type_id         = $request->company_type_id;
                $insurance->card_number     = $request->card_number;
                $insurance->expiry_date     = $expiry_date;
                $insurance->max_ceilling    = $request->max_ceilling;
                $insurance->deduct_amount   = $request->deduct_amount;
                $insurance->co_pay_amount   = $request->co_pay_amount;
                $insurance->co_pay_all_service  = $request->co_pay_all_service;
                $insurance->is_status           = $request->is_status;
                $insurance->with_consultation = $request->with_consultation;
                $insurance->updated_by          = $request->user_id;
                $insurance->updated_at          = date('Y-m-d H:i:s');
                $insurance->update();
                
                if($insurance) {
                    foreach($categories as $desc) {
                        $department_id = $desc['department_id']; 
                        $patient = PatientInsuranceDetail::SELECT('id', 'patient_detail_id')
                                ->where(['patient_detail_id' => $patient_detail_id, 'department_id' =>$department_id, 'patient_insurance_id'=>$patient_insurance_id, 'is_active' => 1])->get();
                        if($patient->count() == 0) {
                            $detail = PatientInsuranceDetail::create([
                                'patient_detail_id' => $request->patient_detail_id,
                                'patient_insurance_id' => $patient_insurance_id,
                                'department_id' => $desc['department_id'],
                                'co_pay_percentage' => $desc['co_pay_percentage'],
                                'deductible_amount' => $desc['deductible_amount'],
                                'per_invoice' => $desc['per_invoice'],
                                'max_ceilling' => $desc['max_ceilling'],
                                'created_by' => $request->user_id,
                                'updated_by' => $request->user_id,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                        } else {
                            $update = PatientInsuranceDetail::where(['patient_detail_id' => $patient_detail_id, 'department_id'=> $department_id, 'patient_insurance_id'=>$patient_insurance_id])
                            ->update([
                                'co_pay_percentage' => $desc['co_pay_percentage'],
                                'deductible_amount' => $desc['deductible_amount'],
                                'per_invoice' => $desc['per_invoice'],
                                'max_ceilling' => $desc['max_ceilling'],
                                'updated_by'=> $request->user_id, 
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                    return $this->sendResponse(1, 200, 'Patient Insurance detail updated.', 'patient_insurance_id', $patient_insurance_id);
                } else {
                    return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.');
                }
       
            }
           
        } catch(\Exception $e) {
            Log::debug("API SavePatientInsurance:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }
    }

    public function CheckMrNumber(Request $request) {
        try{

            $validator = Validator::make($request->all(), [
                'user_id'                   => 'required|integer|exists:users,id',
                'patient_detail_id'         => 'required|integer',
                'mr_number'                 => 'required|min:3|max:20'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            $patient_detail_id = $request->patient_detail_id;
            if($patient_detail_id == 0) {
                $is_exist_mr_number = PatientDetail::where(['mr_number'=>$request->mr_number, 'is_active' => 1])->get();
                if($is_exist_mr_number->count() != 0 ) {
                    return $this->sendResponse(0, 200, 'MR Number already exist'); 
                } 
            } else if($patient_detail_id != 0) {
                $is_exist_mr_number = PatientDetail::where(['mr_number'=>$request->mr_number, 'is_active' => 1])
                    ->where('id', '!=' , $patient_detail_id)->get();
                if($is_exist_mr_number->count() != 0 ) {
                    return $this->sendResponse(0, 200, 'MR Number already exist'); 
                }
            } 
                
            return $this->sendResponse(1, 200, 'Success'); 
            

        } catch(\Exception $e) {
            Log::debug("API CheckMrNumber:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }
    }

    public function CheckPolicyNumber(Request $request) {
        try{

            $validator = Validator::make($request->all(), [
                'user_id'                   => 'required|integer|exists:users,id',
                'patient_insurance_id'      => 'required|integer',
                'card_number'               => 'required|min:5|max:25'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            $patient_insurance_id = $request->patient_insurance_id;
            if($patient_insurance_id == 0) {
                $is_exist_card_number = PatientInsurance::where(['card_number'=>$request->card_number, 'is_active' => 1])->get();
                if($is_exist_card_number->count() != 0 ) {
                    return $this->sendResponse(0, 200, 'Insurance Card Number already exist'); 
                } 
            } else if($patient_insurance_id != 0) {
                $is_exist_card_number = PatientInsurance::where(['card_number'=>$request->card_number, 'is_active' => 1])
                    ->where('id', '!=' , $patient_insurance_id)->get();
                if($is_exist_card_number->count() != 0 ) {
                    return $this->sendResponse(0, 200, 'Insurance Card Number already exist'); 
                }
            } 
                
            return $this->sendResponse(1, 200, 'Success'); 
            

        } catch(\Exception $e) {
            Log::debug("API CheckPolicyNumber:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }
    }
    
    public function DeletePatientInsurance(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'patient_detail_id' => 'required|integer|exists:patient_details,id,is_active,1',
                'patient_insurance_id' => 'required|integer|exists:patient_insurances,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $patient_detail_id = $request->patient_detail_id;
            $patient_insurance_id = $request->patient_insurance_id;

            $is_exist = PatientInsurance::where(['id'=> $patient_insurance_id, 'patient_detail_id'=>$patient_detail_id, 'is_active' => 1])->get();
            if($is_exist->count() == 0) {
                return $this->sendResponse(0, 200, 'Patient insurance record not found'); 
            }

            $update = PatientInsurance::where(['patient_detail_id' => $patient_detail_id, 'id'=> $patient_insurance_id])
                    ->update([
                        'is_active' => 0,
                        'updated_by'=> $request->user_id, 
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            
            $is_exist2 = PatientInsuranceDetail::where(['patient_insurance_id'=> $patient_insurance_id, 'patient_detail_id'=>$patient_detail_id, 'is_active' => 1])->get();
            if($is_exist2->count() != 0) {
               
                $update_detail = PatientInsuranceDetail::where(['patient_detail_id' => $patient_detail_id, 'patient_insurance_id'=> $patient_insurance_id])
                    ->update([
                        'is_active' => 0,
                        'updated_by'=> $request->user_id, 
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            }

            if($update) {
                //$delete_logs = $this->DeleteLogs($request->user_id, $doctor->id, 'MasterLog', 'ReferralDoctor', 'Description');
                return $this->sendResponse(1,200, 'Patient insurance deleted successfully', 'patient_insurance_id', $patient_insurance_id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
        } catch(\Exception $e) {
            Log::debug("API DeletePatientInsurance:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }

    }

    public function SearchPatientRelation(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'               => 'required|integer|exists:users,id',
                'hospital_detail_id'    => 'required|integer',
                'search_from'           => 'required|string|in:Guardian,Kin',
                'search_field'          => 'required|string|in:firstname,middlename,lastname,mr_number,mobile_no',
                'search_value'          => 'required|string|min:3|max:20'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($request->search_from == 'Guardian') {
                $table = 'view_patient_guardians';
            } else {
                $table = 'view_patient_kin';
            }
            $search_field = $request->search_field;
            $search_value = $request->search_value;
            if($request->hospital_detail_id == 0) {
                $patients = DB::select("SELECT `mr_number`, `firstname`, `middlename`, `lastname`, `relationship_id`, `relationship_name`, `city_name`, `city_id`, `region_name`, `region_id`, `country_name`, `country_id`, `address`, `mobile_no`, `email`, `post_box_no`, `landline_number`, `office_phone`, `emirate_ids` FROM $table WHERE  $search_field LIKE '%$search_value%' ORDER BY id DESC LIMIT 20 ");
            } else {
                $hospital_detail_id = $request->hospital_detail_id;
                $patients = DB::select("SELECT `mr_number`, `firstname`, `middlename`, `lastname`, `relationship_id`, `relationship_name`, `city_name`, `city_id`, `region_name`, `region_id`, `country_name`, `country_id`, `address`, `mobile_no`, `email`, `post_box_no`, `landline_number`, `office_phone`, `emirate_ids` FROM $table  WHERE `hospital_detail_id` = $hospital_detail_id AND  $search_field LIKE '%$search_value%' ORDER BY id DESC LIMIT 20 ");
            }
            return response()->json($patients);

        } catch(\Exception $e) {
            Log::debug("API SearchPatientRelation:: ".$e->getMessage());
            return $this->sendResponse(0, 200, $e->getMessage(), 'error', 'Something went wrong try again after sometime.');
        }
    }


    public function SearchPatientName(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'hospital_detail_id'    => 'required|integer',
                'search_value'          => 'required|string|min:3|max:20'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            // if($this->VerifyPageAccess('patient', 'is_view') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }

            $hospital_detail_id = $request->hospital_detail_id;
            $search_value = $request->search_value;

            $start = 0;
            $page_limit = 1000;
            $patient =  DB::select("SELECT id, mr_number, first_name, middle_name, last_name, profile_image,  primary_contact, emirate_ids FROM view_patient_details WHERE hospital_detail_id = $hospital_detail_id AND full_name LIKE '%$search_value%' OR mr_number LIKE '%$search_value%' OR primary_contact LIKE '%$search_value%' ORDER BY id DESC LIMIT 50");
           
            $image_path = config('app.image_path');

            $resp =[];
            $resp['image_path'] = $image_path.'patient/';
            $resp['patient'] = $patient;

            return $this->sendResponse(1,200, 'Success', 'data', $resp);
        } catch(\Exception $e) {
            Log::debug("API SearchPatientName:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetPatientProfile($id) {
        try {
            $patient =  DB::select("SELECT id, mr_number, title_name, first_name, middle_name, last_name, full_name, date_of_birth, patient_age, nationality_name, city_name, region_name, country_name, primary_contact_code, primary_contact, profile_image, emirate_ids FROM view_patient_details WHERE id = $id  LIMIT 1");
           
            $image_path = config('app.image_path');

            $resp =[];
            $resp['image_path'] = $image_path.'patient/';
            $resp['patient'] = $patient;

            return $this->sendResponse(1,200, 'Success', 'data', $resp);

        } catch(\Exception $e) {
            Log::debug("API GetPatientProfile:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetPatientInsurance($id) {
        try {
            $insurance =  ViewPatientInsurance::SELECT('id','patient_detail_id', 'policy_holder', 'card_number', 'expiry_date', 'ins_plan_name', 'main_company_name')
                        ->WHERE(['patient_detail_id' => $id])->get();
            if($insurance->count() != 0) {
                return $this->sendResponse(1,200, 'Success', 'data', $insurance);
            } else {
                return $this->sendResponse(0,200, 'No Insurance');
            }
 
        } catch(\Exception $e) {
            Log::debug("API GetPatientPolicy:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

}