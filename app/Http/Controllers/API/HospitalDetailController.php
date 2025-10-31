<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\HospitalDetail;
use App\HospitalSetting;
use App\HealthAuthority;
use App\ViewHospitalDetail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use \Validator;

class HospitalDetailController extends BaseController
{
    public function GetClientDetailData(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'role_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $image_path = config('app.image_path');
                if($request->role_id === 1) {
                    $client = ViewHospitalDetail::SELECT('id', 'english_name', 'arabic_name', 'short_name', 'address_line1', 'address_line2', 'email', 'webiste_url', 'facility_license_no', 'city_name', 'region_name', 'country_name', 'phone_number', 'fax_number', 'location_url', 'username', 'authority_region_name', 'web_service_url', 'small_logo', 'header_logo')->ORDERBY('id', 'DESC')->get();
                    $response = [];
                    $response['image_path'] = $image_path.'hospital_logo/';
                    $response['client_list'] = $client;
                    return $this->sendResponse(1, 200, 'Success', 'data', $response);
                } else {
                    return $this->sendResponse(2, 200, 'Unautorized user. Logout Then Login again to continue', '');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug("API GetClientDetailData:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetBranchList(Request $request) {
        try {

            $client = ViewHospitalDetail::SELECT('id', 'english_name', 'short_name')->get();

            return $this->sendResponse(1, 200, 'Success', 'data', $client);
            
            
        } catch(\Exception $e) {
            Log::debug("API GetBranchList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function CreateClient(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'=>'required|integer',
                'english_name' => 'required|string|min:3|max:150',
                'arabic_name' => 'min:0|max:150',
                'address_line1' => 'required|string|min:3|max:200',
                'address_line2' => 'min:0|max:200',
                'email'         => 'min:0|max:50',
                'country_id' => 'required|integer',
                'city_id' => 'required|integer',
                'region_id' => 'required|integer',
                'webiste_url' => 'min:0|max:100',
                'phone_number' => 'required|min:3|max:16',
                'fax_number' => 'min:0|max:30',
                'location_url' => 'min:0|max:100',
                'short_name' => 'required|string|min:3|max:30',
                'small_logo' => 'mimes:jpeg,jpg,png|required|max:5120',
                'header_logo' => 'mimes:jpeg,jpg,png|required|max:5120',
                'authority_region_id' => 'required|integer', //health authority region
                'facility_license_no' => 'required|max:30',
                'username' => 'min:0|max:30',
                'password' => 'min:0|max:30',
                'web_service_url' => 'min:0|max:200',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $hospital_count = HospitalDetail::where(['name' => $request->english_name, 'is_active' => 1])->get();
                $authority_count = HealthAuthority::where(['facility_license_no' => $request->facility_license_no, 'is_active' => 1])->get();
                if($hospital_count->count() == 0) {
                    if($authority_count->count() == 0) {
                        $header_logo_name = '';
                        $small_logo_name = '';
                        if($request->hasFile('header_logo'))
                        {
                            $header_logo = $request->file('header_logo');
                            $extension = $request->file('header_logo')->extension();
                            $header_logo_name = time().'1_'.str_replace(' ', '_',$request->short_name).'.'.$extension;                    
                            $destinationPath = public_path('/hospital_logo');
                            $header_logo->move($destinationPath, $header_logo_name);
                        }

                        if($request->hasFile('small_logo'))
                        {
                            $small_logo = $request->file('small_logo');
                            $extension = $request->file('small_logo')->extension();
                            $small_logo_name = time().'2_'.str_replace(' ', '_',$request->short_name).'.'.$extension;                     
                            $destinationPath = public_path('/hospital_logo');
                            $small_logo->move($destinationPath, $small_logo_name);
                        }

                        $hospital = HospitalDetail::create([
                            'name' => $request->english_name,
                            'arabic_name' => $request->arabic_name,
                            'address_line1' => $request->address_line1,
                            'address_line2' => $request->address_line2,
                            'email' => $request->email,
                            'city_id' => $request->city_id,
                            'country_id' => $request->country_id,
                            'region_id' => $request->region_id,
                            'short_name' => $request->short_name,
                            'webiste_url' => $request->webiste_url,
                            'phone_number' => $request->phone_number,
                            'fax_number' => $request->fax_number,
                            'location_url' => $request->location_url,
                            'header_logo' => $header_logo_name,
                            'small_logo' => $small_logo_name,
                            'created_by' => $request->user_id,
                            'updated_by' => $request->user_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        if($hospital->id) {
                                $save_authority = $this->SaveHealthAuthority(0, $hospital->id, $request->facility_license_no, $request->authority_region_id, $request->username, $request->password, $request->web_service_url, $request->user_id);
                            $resp = [];
                            $resp['hospital_id'] = $hospital->id;
                            $resp['authority_id'] = $save_authority;
                            return $this->sendResponse(1,200, 'Client created successfully', 'data', $resp);
                        } else {
                            return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                        }

                    } else {
                        return $this->sendResponse(0,200, 'Facility License number already exist', '');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Hospital name already exist', '');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API CreateClient:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function UpdateClient(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'=>'required|integer',
                'hospital_id' => 'required|integer',
                'health_autority_id' => 'required|integer',
                'english_name' => 'required|string|min:3|max:150',
                'arabic_name' => 'min:0|max:150',
                'address_line1' => 'required|string|min:3|max:200',
                'address_line2' => 'min:0|max:200',
                'email'         => 'min:0|max:50',
                'country_id' => 'required|integer',
                'city_id' => 'required|integer',
                'region_id' => 'required|integer',
                'webiste_url' => 'min:0|max:100',
                'phone_number' => 'required|min:3|max:16',
                'fax_number' => 'min:0|max:30',
                'location_url' => 'min:0|max:100',
                'short_name' => 'required|string|min:3|max:30',
                'authority_region_id' => 'required|integer', //health authority region
                'facility_license_no' => 'required|max:30',
                'username' => 'min:0|max:30',
                'password' => 'min:0|max:30',
                'web_service_url' => 'min:0|max:200',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $hospital_count = HospitalDetail::where(['name' => $request->english_name, 'is_active' => 1])
                            ->where('id', '!=', $request->hospital_id)->get();
                $authority_count = HealthAuthority::where(['facility_license_no' => $request->facility_license_no, 'is_active' => 1])
                            ->where('id', '<>', $request->health_autority_id)->get();
                if($hospital_count->count() == 0) {
                    if($authority_count->count() == 0) {
                        $header_logo_name = '';
                        $small_logo_name = '';
                        $is_header_logo = 0;
                        $is_small_logo = 0;
                        if($request->hasFile('header_logo'))
                        {
                            $header_logo = $request->file('header_logo');
                            $extension = $request->file('header_logo')->extension();
                            $header_logo_name = time().'1_'.str_replace(' ', '_',$request->short_name).'.'.$extension;                    
                            $destinationPath = public_path('/hospital_logo');
                            $header_logo->move($destinationPath, $header_logo_name);
                            $is_header_logo = 1;
                        }

                        if($request->hasFile('small_logo'))
                        {
                            $small_logo = $request->file('small_logo');
                            $extension = $request->file('small_logo')->extension();
                            $small_logo_name = time().'2_'.str_replace(' ', '_',$request->short_name).'.'.$extension;                     
                            $destinationPath = public_path('/hospital_logo');
                            $small_logo->move($destinationPath, $small_logo_name);
                            $is_small_logo = 1;
                        }

                        $hospital = HospitalDetail::find($request->hospital_id);
                        $hospital->name= $request->english_name;
                        $hospital->arabic_name= $request->arabic_name;
                        $hospital->address_line1= $request->address_line1;
                        $hospital->address_line2= $request->address_line2;
                        $hospital->email= $request->email;
                        $hospital->city_id= $request->city_id;
                        $hospital->country_id= $request->country_id;
                        $hospital->region_id= $request->region_id;
                        $hospital->short_name= $request->short_name;
                        $hospital->webiste_url= $request->webiste_url;
                        $hospital->phone_number= $request->phone_number;
                        $hospital->fax_number= $request->fax_number;
                        $hospital->location_url= $request->location_url;
                        if($is_header_logo == 1){
                            $hospital->header_logo= $header_logo_name;
                        }
                        if($is_small_logo == 1) {
                            $hospital->small_logo= $small_logo_name;
                        }
                        $hospital->updated_by= $request->user_id;
                        $hospital->updated_at= date('Y-m-d H:i:s');
                        $hospital->update();

                        $save_authority = $this->SaveHealthAuthority($request->health_autority_id, $request->hospital_id, $request->facility_license_no, $request->authority_region_id, $request->username, $request->password, $request->web_service_url, $request->user_id);
                        $resp = [];
                        $resp['hospital_id'] = $request->hospital_id;
                        $resp['authority_id'] = $request->health_autority_id;
                        return $this->sendResponse(1,200, 'Client details updated successfully', 'data', $resp);
                        
                    } else {
                        return $this->sendResponse(0,400, 'Facility License number already exist', 'count', $hospital_count->count());
                    }
                } else {
                    return $this->sendResponse(0,400, 'Client name already exist', 'count', $hospital_count->count());
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API CreateClient:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function SaveHealthAuthority($authority_id, $hospital_id, $facility_no, $region_id, $username, $password, $web_service_url, $user_id) {
        try {
            if($authority_id == 0){
                $authority = HealthAuthority::create([
                    'hospital_detail_id' => $hospital_id,
                    'region_id' => $region_id,
                    'facility_license_no' => $facility_no,
                    'username' => $username,
                    'password' => $password,
                    'web_service_url' => $web_service_url,
                    'created_by' => $user_id,
                    'updated_by' => $user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                return $authority->id;
            } else {
                $authority = HealthAuthority::find($authority_id);
                $authority->region_id = $region_id;
                $authority->facility_license_no =$facility_no;
                $authority->username = $username;
                $authority->password = $password;
                $authority->web_service_url = $web_service_url;
                $authority->updated_by = $user_id;
                $authority->updated_at = date('Y-m-d H:i:s');
                $authority->update();
                return $authority_id;
            }

            return true;
        } catch(\Exception $e) {
            Log::debug("API SaveHealthAuthority:: ".$e->getMessage());
            return false;
        }
    }


    public function GetSingleClient(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'hospital_id' => 'required|integer',
                'role_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 

            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $image_path = config('app.image_path');
                if($request->role_id === 1) {
                    $client = ViewHospitalDetail::SELECT('id as hospital_id', 'english_name', 'arabic_name', 'short_name',  'address_line1', 'address_line2', 'city_id', 'region_id', 'country_id', 'email', 'webiste_url', 'phone_number','fax_number', 'location_url', 'health_autority_id', 'authority_region_id', 'facility_license_no', 'username', 'password', 'web_service_url','small_logo', 'header_logo', 'city_name', 'region_name', 'country_name', 'authority_region_name')
                    ->where(['id' => $request->hospital_id])->get();
                    if($client->count() == 1){
                        $response = [];
                        $response['image_path'] = $image_path.'hospital_logo/';
                        $response['client'] = $client;
                        return $this->sendResponse(1, 200, 'Success', 'data', $response);
                    } else {
                        return $this->sendResponse(0, 200, 'Record not found', '');
                    }
                } else {
                    return $this->sendResponse(2, 200, 'Unautorized user. Logout Then Login again to continue', '');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug("API GetClientDetailData:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function DeleteClient(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'hospital_id' => 'required|integer',
                'role_id' => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 1) === true){
                if($request->role_id == 1) {
                    $title = HospitalDetail::find($request->hospital_id);
                    $title->is_active = 0;
                    $title->updated_by = $request->user_id;
                    $title->updated_at = date('Y-m-d H:i:s');
                    $title->update();

                    if($request->hospital_id) {
                        return $this->sendResponse(1,200, 'Client deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    } 
                } else {
                    return $this->sendResponse(2, 200, 'Unautorized user. Logout Then Login again to continue', '');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteClient :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetHospitalSettingData($id) {
        try {
            $setting = HospitalSetting::SELECT('id','hospital_detail_id', 'file_serial_number', 'prefix_text', 'prefix_number', 'start_prefix_after_number', 'insurance_detectable', 'email_required', 'referral_doctor_required', 'about_us_required', 'next_kin_required', 'allow_patient_register', 'hl_seven_registration', 'act_before_approval', 'lab_invest_no', 'radiology_invest_no', 'direct_patient_file_prefix', 'direct_patient_file_no', 'direct_patient_bill_prefix', 'direct_patient_bill_no', 'disable_auto_fill_doctor_order', 'appointment_color', 'allow_appolintment_other_doctor', 'change_attend_status_manually', 'center_day_off', 'malaffi_status', 'malaffi_inception_date', 'disable_chat_system', 'not_accept_investication', 'act_lab_radiology', 'service_date_bill_date', 'hide_date_emr', 'allow_consult_dept', 'hide_header_lab_report', 'claim_after_review', 'send_receipt_pharmacy', 'order_date_act_done_date', 'outsource_lab_credit', 'outsource_lab_cash', 'register_as_phc', 'hl7_folder_path', 'dental_chart_folder_path', 'ultra_sound_temp_path', 'patient_folder_path', 'attach_pharmacy_database', 'patient_db_option', 'patient_db_text', 'activate_emr_log')
                ->where(['hospital_detail_id' => $id, 'is_active' => 1])->get();
            if($setting->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $setting);
            } else {
                return $this->sendResponse(0,200, 'Record not found');
            }
        } catch(\Exception $e) {
            Log::debug('API GetHospitalSettingData :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function SaveHospitalSetting(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'=>'required|integer',
                'hospital_detail_id'=>'required|integer', 
                'file_serial_number'=>'nullable|integer|in:1,2,3', 
                'prefix_text'=>'nullable|min:1:max:10', 
                'prefix_number' => 'nullable', 
                'start_prefix_after_number'=>'required|integer|in:1,0', 
                'insurance_detectable'=>'nullable|integer|in:1,2', 
                'email_required'=>'required|integer|in:1,0', 
                'referral_doctor_required'=>'required|integer|in:1,0', 
                'about_us_required'=>'required|integer|in:1,0',
                'next_kin_required'=>'required|integer|in:1,0', 
                'allow_patient_register'=>'required|integer|in:1,0', 
                'hl_seven_registration'=>'required|integer|in:1,0', 
                'act_before_approval'=>'required|integer|in:1,0', 
                'lab_invest_no'=>'nullable', 
                'radiology_invest_no'=>'nullable', 
                'direct_patient_file_prefix'=>'nullable', 
                'direct_patient_file_no'=>'nullable|integer', 
                'direct_patient_bill_prefix'=>'nullable', 
                'direct_patient_bill_no'=>'nullable|integer', 
                'disable_auto_fill_doctor_order'=>'required|integer|in:1,0', 
                'appointment_color'=>'required|integer|in:1,0', 
                'allow_appolintment_other_doctor'=>'required|integer|in:1,0', 
                'change_attend_status_manually'=>'required|integer|in:1,0', 
                'center_day_off'=>'nullable|integer|in:0,1,2,3,4,5,6', 
                'malaffi_status'=>'required|integer|in:1,0', 
                'malaffi_inception_date'=>'nullable|date', 
                'disable_chat_system'=>'required|integer|in:1,0', 
                'not_accept_investication'=>'required|integer|in:1,0', 
                'act_lab_radiology'=>'required|integer|in:1,0', 
                'service_date_bill_date'=>'required|integer|in:1,0', 
                'hide_date_emr'=>'required|integer|in:1,0', 
                'allow_consult_dept'=>'required|integer|in:1,0', 
                'hide_header_lab_report'=>'required|integer|in:1,0', 
                'claim_after_review'=>'required|integer|in:1,0', 
                'send_receipt_pharmacy'=>'required|integer|in:1,0', 
                'order_date_act_done_date'=>'required|integer|in:1,0', 
                'outsource_lab_credit'=>'required|integer|in:1,0', 
                'outsource_lab_cash'=>'required|integer|in:1,0', 
                'register_as_phc'=>'required|integer|in:1,0', 
                'hl7_folder_path' => 'required|string|min:3|max:150',
                'dental_chart_folder_path' => 'required|string|min:3|max:150',
                'ultra_sound_temp_path' => 'required|string|min:3|max:150',
                'patient_folder_path' => 'required|string|min:3|max:150',
                'attach_pharmacy_database'=>'required|integer|in:1,0', 
                'patient_db_option' => 'required|string|min:3|max:150',
                'patient_db_text' => 'required|string|min:3|max:150',
                'activate_emr_log'=>'required|integer|in:1,0',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 1) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $setting = HospitalSetting::SELECT('id', 'hospital_detail_id')
                    ->where(['hospital_detail_id' => $request->hospital_detail_id, 'is_active' => 1])->get();
            if($setting->count() == 0) {
                $hospital = HospitalSetting::create([
                    'hospital_detail_id' => $request->hospital_detail_id, 
                    'file_serial_number' => $request->file_serial_number, 
                    'prefix_text' => $request->prefix_text, 
                    'prefix_number' => $request->prefix_number,
                    'start_prefix_after_number' => $request->start_prefix_after_number, 
                    'insurance_detectable' => $request->insurance_detectable, 
                    'email_required' => $request->email_required, 
                    'referral_doctor_required' => $request->referral_doctor_required, 
                    'about_us_required' => $request->about_us_required, 
                    'next_kin_required' => $request->next_kin_required, 
                    'allow_patient_register' => $request->allow_patient_register, 
                    'hl_seven_registration' => $request->hl_seven_registration, 
                    'act_before_approval' => $request->act_before_approval, 
                    'lab_invest_no' => $request->lab_invest_no, 
                    'radiology_invest_no' => $request->radiology_invest_no, 
                    'direct_patient_file_prefix' => $request->direct_patient_file_prefix, 
                    'direct_patient_file_no' => $request->direct_patient_file_no, 
                    'direct_patient_bill_prefix' => $request->direct_patient_bill_prefix, 
                    'direct_patient_bill_no' => $request->direct_patient_bill_no, 
                    'disable_auto_fill_doctor_order' => $request->disable_auto_fill_doctor_order, 
                    'appointment_color' => $request->appointment_color, 
                    'allow_appolintment_other_doctor' => $request->allow_appolintment_other_doctor, 
                    'change_attend_status_manually' => $request->change_attend_status_manually, 
                    'center_day_off' => $request->center_day_off, 
                    'malaffi_status' => $request->malaffi_status, 
                    'malaffi_inception_date' => $request->malaffi_inception_date, 
                    'disable_chat_system' => $request->disable_chat_system, 
                    'not_accept_investication' => $request->not_accept_investication, 
                    'act_lab_radiology' => $request->act_lab_radiology, 
                    'service_date_bill_date' => $request->service_date_bill_date, 
                    'hide_date_emr' => $request->hide_date_emr, 
                    'allow_consult_dept' => $request->allow_consult_dept, 
                    'hide_header_lab_report' => $request->hide_header_lab_report, 
                    'claim_after_review' => $request->claim_after_review, 
                    'send_receipt_pharmacy' => $request->send_receipt_pharmacy, 
                    'order_date_act_done_date' => $request->order_date_act_done_date, 
                    'outsource_lab_credit' => $request->outsource_lab_credit, 
                    'outsource_lab_cash' => $request->outsource_lab_cash, 
                    'register_as_phc' => $request->register_as_phc, 
                    'hl7_folder_path' => $request->hl7_folder_path, 
                    'dental_chart_folder_path' => $request->dental_chart_folder_path, 
                    'ultra_sound_temp_path' => $request->ultra_sound_temp_path, 
                    'patient_folder_path' => $request->patient_folder_path, 
                    'attach_pharmacy_database' => $request->attach_pharmacy_database, 
                    'patient_db_option' => $request->patient_db_option, 
                    'patient_db_text' => $request->patient_db_text, 
                    'activate_emr_log' => $request->activate_emr_log,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($hospital->id) {
                    return $this->sendResponse(1,200, 'Setting updated successfully');
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }

            } else {
                $hospital = HospitalSetting::find($setting[0]->id);
                $hospital->file_serial_number           = $request->file_serial_number;
                $hospital->prefix_text                  = $request->prefix_text;
                $hospital->prefix_number                = $request->prefix_number;
                $hospital->start_prefix_after_number    = $request->start_prefix_after_number;
                $hospital->insurance_detectable         = $request->insurance_detectable;
                $hospital->email_required               = $request->email_required;
                $hospital->referral_doctor_required     = $request->referral_doctor_required;
                $hospital->about_us_required            = $request->about_us_required;
                $hospital->next_kin_required            = $request->next_kin_required;
                $hospital->allow_patient_register       = $request->allow_patient_register;
                $hospital->hl_seven_registration        = $request->hl_seven_registration;
                $hospital->act_before_approval          = $request->act_before_approval;
                $hospital->lab_invest_no                = $request->lab_invest_no;
                $hospital->radiology_invest_no          = $request->radiology_invest_no;
                $hospital->direct_patient_file_prefix   = $request->direct_patient_file_prefix;
                $hospital->direct_patient_file_no       = $request->direct_patient_file_no;
                $hospital->direct_patient_bill_prefix   = $request->direct_patient_bill_prefix;
                $hospital->direct_patient_bill_no       = $request->direct_patient_bill_no;
                $hospital->disable_auto_fill_doctor_order   = $request->disable_auto_fill_doctor_order;
                $hospital->appointment_color            = $request->appointment_color;
                $hospital->allow_appolintment_other_doctor  = $request->allow_appolintment_other_doctor;
                $hospital->change_attend_status_manually    = $request->change_attend_status_manually;
                $hospital->center_day_off               = $request->center_day_off;
                $hospital->malaffi_status               = $request->malaffi_status;
                $hospital->malaffi_inception_date       = $request->malaffi_inception_date;
                $hospital->disable_chat_system          = $request->disable_chat_system;
                $hospital->not_accept_investication     = $request->not_accept_investication;
                $hospital->act_lab_radiology            = $request->act_lab_radiology;
                $hospital->service_date_bill_date       = $request->service_date_bill_date;
                $hospital->hide_date_emr                = $request->hide_date_emr;
                $hospital->allow_consult_dept           = $request->allow_consult_dept;
                $hospital->hide_header_lab_report       = $request->hide_header_lab_report;
                $hospital->claim_after_review           = $request->claim_after_review;
                $hospital->send_receipt_pharmacy        = $request->send_receipt_pharmacy;
                $hospital->order_date_act_done_date     = $request->order_date_act_done_date;
                $hospital->outsource_lab_credit         = $request->outsource_lab_credit;
                $hospital->outsource_lab_cash           = $request->outsource_lab_cash;
                $hospital->register_as_phc           = $request->register_as_phc;
                $hospital->hl7_folder_path           = $request->hl7_folder_path;
                $hospital->dental_chart_folder_path           = $request->dental_chart_folder_path;
                $hospital->ultra_sound_temp_path           = $request->ultra_sound_temp_path;
                $hospital->patient_folder_path           = $request->patient_folder_path;
                $hospital->attach_pharmacy_database           = $request->attach_pharmacy_database;
                $hospital->patient_db_option           = $request->patient_db_option;
                $hospital->patient_db_text           = $request->patient_db_text;
                $hospital->activate_emr_log             = $request->activate_emr_log;
                $hospital->updated_by = $request->user_id;
                $hospital->updated_at = date('Y-m-d H:i:s');
                $hospital->update();

                if($hospital->id) {
                    return $this->sendResponse(1,200, 'Setting updated successfully');
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            }
                
        } catch(\Exception $e) {
            Log::debug("API SaveHospitalSetting:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function HospitalDetailsSetting(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'hospital_id' => 'nullable|integer', // Optional for create, required for update
                'health_autority_id' => 'nullable|integer', // Optional for create, required for update
                'english_name' => 'required|string|min:3|max:150',
                'arabic_name' => 'nullable|string|max:150',
                'address_line1' => 'required|string|min:3|max:200',
                'email' => 'nullable|string|max:50',
                'country_id' => 'required|integer',
                'city_id' => 'required|integer',
                'region_id' => 'required|integer',
                'webiste_url' => 'nullable|string|max:100',
                'phone_number' => 'required|string|min:3|max:16',
                'fax_number' => 'nullable|string|max:30',
                'location_url' => 'nullable|string|max:100',
                'short_name' => 'required|string|min:3|max:30',
                'small_logo' => 'nullable|mimes:jpeg,jpg,png|max:5120', // Required only for create
                'header_logo' => 'nullable|mimes:jpeg,jpg,png|max:5120', // Required only for create
                'facility_license_no' => 'required|string|max:30',
                'username' => 'required_if:hospital_id,null|string|max:30', // Required only for create
                'password' => 'required_if:hospital_id,null|string|max:30', // Required only for create
                'web_service_url' => 'min:0|max:200',
                'authority_region_id' => 'required|integer', //health authority region
                'tax_identify_no' => 'nullable|string|min:3|max:200', // Required only for create
                'effective_date_vat' => 'nullable|date', // Required only for create
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if ($this->VerifyAuthUser($request->user_id, 1) !== true) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Invalid user or token', '');
            }

            $isUpdate = !empty($request->hospital_id) && !empty($request->hospital_id);

            $header_logo_name = '';
            $small_logo_name = '';
            $is_header_logo = 0;
            $is_small_logo = 0;

            // Handle file uploads
            if ($request->hasFile('header_logo')) {
                $header_logo = $request->file('header_logo');
                $extension = $header_logo->extension();
                $header_logo_name = time() . '1_' . str_replace(' ', '_', $request->short_name) . '.' . $extension;
                $destinationPath = public_path('/hospital_logo');
                $header_logo->move($destinationPath, $header_logo_name);
                $is_header_logo = 1;
            }

            if ($request->hasFile('small_logo')) {
                $small_logo = $request->file('small_logo');
                $extension = $small_logo->extension();
                $small_logo_name = time() . '2_' . str_replace(' ', '_', $request->short_name) . '.' . $extension;
                $destinationPath = public_path('/hospital_logo');
                $small_logo->move($destinationPath, $small_logo_name);
                $is_small_logo = 1;
            }

            if ($isUpdate) {
                // Update existing hospital
                $hospital = HospitalDetail::find($request->hospital_id);
                if (!$hospital) {
                    return $this->sendResponse(0, 200, 'Hospital not found', '');
                }

                $hospital->name = $request->english_name;
                $hospital->arabic_name = $request->arabic_name;
                $hospital->address_line1 = $request->address_line1;
                $hospital->email = $request->email;
                $hospital->city_id = $request->city_id;
                $hospital->country_id = $request->country_id;
                $hospital->region_id = $request->region_id;
                $hospital->short_name = $request->short_name;
                $hospital->webiste_url = $request->webiste_url;
                $hospital->phone_number = $request->phone_number;
                $hospital->fax_number = $request->fax_number;
                $hospital->location_url = $request->location_url;
                $hospital->tax_identify_no = $request->tax_identify_no;
                $hospital->effective_date_vat = $request->effective_date_vat;
                if ($is_header_logo) {
                    $hospital->header_logo = $header_logo_name;
                }
                if ($is_small_logo) {
                    $hospital->small_logo = $small_logo_name;
                }
                $hospital->updated_by= $request->user_id;
                $hospital->updated_at= date('Y-m-d H:i:s');
                $hospital->update();

                $save_authority = $this->SaveHealthAuthority($request->health_autority_id, $request->hospital_id, $request->facility_license_no, $request->authority_region_id, $request->username, $request->password, $request->web_service_url, $request->user_id);
                $resp = [];
                $resp['hospital_id'] = $request->hospital_id;
                $resp['authority_id'] = $request->hospital_id;
                return $this->sendResponse(1,200, 'Hospital details updated successfully', 'data', $resp);
            } else {
                
                // Check for duplicate hospital name and facility license number
                $hospitalQuery = HospitalDetail::where(['name' => $request->english_name, 'is_active' => 1]);
                $authorityQuery = HealthAuthority::where(['facility_license_no' => $request->facility_license_no, 'is_active' => 1]);
    
                if ($isUpdate) {
                    $hospitalQuery->where('id', '!=', $request->hospital_id);
                    $authorityQuery->where('id', '!=', $request->hospital_id);
                }
    
                $hospital_count = $hospitalQuery->count();
                $authority_count = $authorityQuery->count();
    
                if ($hospital_count > 0) {
                    return $this->sendResponse(0, 200, 'Hospital name already exists', '');
                }
    
                if ($authority_count > 0) {
                    return $this->sendResponse(0, 200, 'Facility License number already exists', '');
                }
                // Additional validation for create
                if (!$request->hasFile('header_logo') || !$request->hasFile('small_logo')) {
                    return $this->sendResponse(0, 200, 'Header logo and small logo are required for new clients', '');
                }

                // Create new hospital
                $hospital = HospitalDetail::create([
                    'name' => $request->english_name,
                    'arabic_name' => $request->arabic_name,
                    'address_line1' => $request->address_line1,
                    'email' => $request->email,
                    'city_id' => $request->city_id,
                    'country_id' => $request->country_id,
                    'region_id' => $request->region_id,
                    'short_name' => $request->short_name,
                    'webiste_url' => $request->webiste_url,
                    'phone_number' => $request->phone_number,
                    'fax_number' => $request->fax_number,
                    'location_url' => $request->location_url,
                    'header_logo' => $header_logo_name,
                    'small_logo' => $small_logo_name,
                    'tax_identify_no' => $request->tax_identify_no,
                    'effective_date_vat' => $request->effective_date_vat,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($hospital->id) {
                    $save_authority = $this->SaveHealthAuthority(
                        0, 
                        $hospital->id, 
                        $request->facility_license_no,
                        $request->authority_region_id, 
                        $request->username, 
                        $request->password, 
                        $request->web_service_url, 
                        $request->user_id
                    );
                    $resp = [];
                    $resp['hospital_id'] = $hospital->id;
                    $resp['authority_id'] = $save_authority;
                    return $this->sendResponse(1,200, 'Hospital Details created successfully', 'data', $resp);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            }
        } catch (\Exception $e) {
            Log::debug("API ManageClient:: " . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    // public function GetHospitalDetails(Request $request) {
    //     try {

    //         $validator = Validator::make($request->all(), [
    //             'user_id' => 'required|integer',
    //             'hospital_id' => 'required|integer',
    //             'role_id' => 'required|integer'
    //         ]);
    //         if ($validator->fails()) {
    //             return $this->sendResponse(0, 200, $validator->errors()->first(), '');
    //         } 

    //         if($this->VerifyAuthUser($request->user_id, 1) === true){
    //             $image_path = config('app.image_path');
    //             if($request->role_id === 1) {
    //                 $client = ViewHospitalDetail::SELECT('id as hospital_id', 'english_name', 'arabic_name', 'short_name',  'address_line1', 'city_id', 'region_id', 'country_id', 'email', 'webiste_url', 'phone_number','fax_number', 'location_url', 'health_autority_id', 'authority_region_id', 'facility_license_no', 'username', 'password', 'web_service_url','small_logo', 'header_logo', 'city_name', 'region_name', 'country_name', 'authority_region_name')
    //                 ->where(['id' => $request->hospital_id])->get();
    //                 if($client->count() == 1){
    //                     $response = [];
    //                     $response['image_path'] = $image_path.'hospital_logo/';
    //                     $response['client'] = $client;
    //                     return $this->sendResponse(1, 200, 'Success', 'data', $response);
    //                 } else {
    //                     return $this->sendResponse(0, 200, 'Record not found', '');
    //                 }
    //             } else {
    //                 return $this->sendResponse(2, 200, 'Unautorized user. Logout Then Login again to continue', '');
    //             }
    //         } else {
    //             return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
    //         }

    //     } catch(\Exception $e) {
    //         Log::debug("API GetClientDetailData:: ".$e->getMessage());
    //         return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
    //     }
    // }
    
    public function GetHospitalDetails($id)
    {
        try {
            $setting = ViewHospitalDetail::select([
                'id as hospital_id',
                'english_name',
                'arabic_name',
                'short_name',
                'address_line1',
                'city_id',
                'region_id',
                'country_id',
                'email',
                'webiste_url',
                'phone_number',
                'fax_number',
                'location_url',
                'health_autority_id',
                'authority_region_id',
                'facility_license_no',
                'username',
                'password',
                'web_service_url',
                'small_logo',
                'header_logo',
                'city_name',
                'region_name',
                'country_name',
                'authority_region_name',
                'tax_identify_no',
                'effective_date_vat'
            ])
                ->where('id', $id)
                ->first();

            if ($setting) {
                // Construct full URLs for logo images
                $baseUrl = config('app.image_path') . 'hospital_logo/';
                $setting->header_logo = $setting->header_logo ? $baseUrl . $setting->header_logo : null;
                $setting->small_logo = $setting->small_logo ? $baseUrl . $setting->small_logo : null;

                return $this->sendResponse(1, 200, 'Success', 'data', $setting);
            } else {
                return $this->sendResponse(0, 200, 'Record not found');
            }
        } catch (\Exception $e) {
            // Log the error without accessing $setting if it's undefined
            $query = isset($setting) ? $setting->toSql() : 'Query not executed';
            Log::debug('API GetHospitalSettingData :: Query: ' . $query . ' | Error: ' . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
}
