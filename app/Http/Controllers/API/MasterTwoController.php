<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\EnquiryReason;
use App\EnquiryService;
use App\ReferralClinic;
use App\ReferralDoctor;
use App\ViewReferralDoctor;
use App\ViewReferralClinic;
use App\InsuranceNetwork;
use App\ViewInsuranceNetwork;
use App\InsurancePackage;
use App\ViewInsurancePackage;
use App\InsurancePlan;
use App\ViewInsurancePlan;
use App\AppointmentStatus;
use App\SmsTemplate;
use App\PlanDetail;
use App\PlanDetailDescription;
use App\ViewPlanDetailDescription;
use App\ViewPlanDetail;
use App\Department;
use App\DeptCategory;
use App\RegComments;
use App\ViewRegComments;
use App\TypesAbuse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use \Validator;

class MasterTwoController extends BaseController
{
    //Referral Doctor
    public function ViewReferralDoctor(Request $request) {
        try {

            $doctor = ViewReferralDoctor::SELECT('id', 'name', 'license_no', 'qualification', 'clinic_name', 'city_name', 'region_name', 'country_name', 'address', 'mobile_no', 'email', 'phone_no', 'is_status')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $doctor);
            
        } catch(\Exception $e) {
            Log::debug("API ViewReferralDoctor:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetReferralDoctorList(Request $request) {
        try {

            $doctor = ViewReferralDoctor::SELECT('id', 'name', 'clinic_name')
                        ->WHERE(['is_status' => 'Active'])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $doctor);
            
        } catch(\Exception $e) {
            Log::debug("API GetReferralDoctorList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddReferralDoctor(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'name'          => 'required|string|min:3|max:30',
                'referral_clinic_id' => 'required|integer|exists:referral_clinics,id',
                'qualification' => 'nullable|string|min:0|max:30',
                'license_no'    => 'nullable|string|min:0|max:30',
                'address'       => 'nullable|string|min:0|max:200',
                'phone_no'      => 'nullable|string|min:0|max:20',
                'mobile_no'     => 'nullable|string|min:0|max:20',
                'email'         => 'nullable|string|min:0|max:35',
                'is_status'     => 'required|string|in:Active,In-Active',
                'country_id'    => 'nullable|integer|exists:countries,id',
                'city_id'       => 'nullable|integer|exists:cities,id',
                'region_id'     => 'nullable|integer|exists:regions,id', 
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $doctor = ReferralDoctor::create([
                'name' => $request->name,
                'referral_clinic_id'=> $request->referral_clinic_id,
                'qualification'     => $request->qualification,
                'license_no'        => $request->license_no,
                'address'           => $request->address,
                'phone_no'          => $request->phone_no,
                'mobile_no'         => $request->mobile_no,
                'email'             => $request->email,
                'is_status'         => $request->is_status,
                'country_id'        => $request->country_id,
                'region_id'         => $request->region_id,
                'city_id'           => $request->city_id,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($doctor->id) {
                return $this->sendResponse(1,200, 'Doctor added successfully', 'doctor_id', $doctor->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddReferralDoctor:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleReferralDoctor($id) {
        try {
            $doctor = ReferralDoctor::SELECT('id', 'name', 'license_no', 'referral_clinic_id', 'qualification', 'address', 'country_id', 'region_id', 'city_id', 'phone_no', 'mobile_no', 'email', 'is_status')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($doctor->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $doctor);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $doctor->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleReferralDoctor :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateReferralDoctor(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'doctor_id'     => 'required|integer|exists:referral_doctors,id,is_active,1',
                'name'          => 'required|string|min:3|max:30',
                'referral_clinic_id' => 'required|integer|exists:referral_clinics,id',
                'qualification' => 'nullable|string|min:0|max:30',
                'license_no'    => 'nullable|string|min:0|max:30',
                'address'       => 'nullable|string|min:0|max:200',
                'phone_no'      => 'nullable|string|min:0|max:20',
                'mobile_no'     => 'nullable|string|min:0|max:20',
                'email'         => 'nullable|string|min:0|max:35',
                'is_status'     => 'required|string|in:Active,In-Active',
                'country_id'    => 'nullable|integer|exists:countries,id',
                'city_id'       => 'nullable|integer|exists:cities,id',
                'region_id'     => 'nullable|integer|exists:regions,id', 
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }
            $doctor_id = $request->doctor_id;
            $old_value = ReferralDoctor::SELECT('id', 'name', 'license_no', 'referral_clinic_id', 'qualification', 'address', 'country_id', 'region_id', 'city_id', 'phone_no', 'mobile_no', 'email', 'is_status')
            ->where(['id' => $doctor_id])->get();
            $doctor = ReferralDoctor::find($request->doctor_id);
            $doctor->name = $request->name;
            $doctor->referral_clinic_id= $request->referral_clinic_id;
            $doctor->qualification     = $request->qualification;
            $doctor->license_no        = $request->license_no;
            $doctor->address           = $request->address;
            $doctor->phone_no          = $request->phone_no;
            $doctor->mobile_no         = $request->mobile_no;
            $doctor->email             = $request->email;
            $doctor->is_status         = $request->is_status;
            $doctor->country_id        = $request->country_id;
            $doctor->region_id         = $request->region_id;
            $doctor->city_id           = $request->city_id;
            $doctor->updated_by = $request->user_id;
            $doctor->updated_at = date('Y-m-d H:i:s');
            $doctor->update();

            if($doctor->id) {
                $field_names = [
                    'name' => 'Doctor name updated', 
                    'referral_clinic_id' => 'Referral clinic changed', 
                    'qualification' => 'Qualification updated', 
                    'license_no' => 'License number updated', 
                    'address' => 'Address details updated', 
                    'phone_no' => 'Phone number updated', 
                    'mobile_no' => 'Mobile number updated', 
                    'email' => 'Email ID updated', 
                    'is_status' => 'Status updated', 
                    'country_id' => 'Country name updated', 
                    'region_id' => 'Region name updated', 
                    'city_id' => 'City name updated'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $doctor_id, 'MasterLog', 'ReferralDoctor', $old_value, $doctor, $field_names);
                return $this->sendResponse(1,200, 'Doctor updated successfully', 'doctor_id', $doctor->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddReferralDoctor:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteReferralDoctor(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'doctor_id' => 'required|integer|exists:referral_doctors,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $doctor = ReferralDoctor::find($request->doctor_id);
            $doctor->is_active = 0;
            $doctor->updated_by = $request->user_id;
            $doctor->updated_at = date('Y-m-d H:i:s');
            $doctor->update();

            if($doctor->id) {
                $delete_logs = $this->DeleteLogs($request->user_id, $doctor->id, 'MasterLog', 'ReferralDoctor', 'Description');
                return $this->sendResponse(1,200, 'Doctor deleted successfully', 'doctor_id', $doctor->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API Deletedoctor:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    //Referral Clinics
    public function ViewReferralClinic(Request $request) {
        try {

            $doctor = ViewReferralClinic::SELECT('id', 'name', 'city_name', 'region_name', 'country_name', 'address', 'mobile_no', 'email', 'phone_no')
                        ->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $doctor);
            
        } catch(\Exception $e) {
            Log::debug("API ViewReferralClinic:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetReferralClinicList(Request $request) {
        try {

            $clinic = ViewReferralClinic::SELECT('id', 'name')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $clinic);
            
        } catch(\Exception $e) {
            Log::debug("API GetReferralClinicList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddReferralClinic(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'name'          => 'required|string|min:3|max:50',
                'address'       => 'nullable|string|min:0|max:200',
                'phone_no'      => 'nullable|string|min:0|max:20',
                'mobile_no'     => 'nullable|string|min:0|max:20',
                'email'         => 'nullable|string|min:0|max:35',
                'country_id'    => 'nullable|integer|exists:countries,id',
                'city_id'       => 'nullable|integer|exists:cities,id',
                'region_id'     => 'nullable|integer|exists:regions,id', 
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $clinic = ReferralClinic::create([
                'name' => $request->name,
                'address'           => $request->address,
                'phone_no'          => $request->phone_no,
                'mobile_no'         => $request->mobile_no,
                'email'             => $request->email,
                'country_id'        => $request->country_id,
                'region_id'         => $request->region_id,
                'city_id'           => $request->city_id,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($clinic->id) {
                return $this->sendResponse(1,200, 'Clinic added successfully', 'clinic_id', $clinic->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddReferralClinic:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleReferralClinic($id) {
        try {
            $clinic = ReferralClinic::SELECT('id', 'name', 'address', 'country_id', 'region_id', 'city_id', 'phone_no', 'mobile_no', 'email')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($clinic->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $clinic);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $clinic->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleReferralClinic :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateReferralClinic(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'clinic_id'     => 'required|integer|exists:referral_clinics,id,is_active,1',
                'name'          => 'required|string|min:3|max:30',
                'address'       => 'nullable|string|min:0|max:200',
                'phone_no'      => 'nullable|string|min:0|max:20',
                'mobile_no'     => 'nullable|string|min:0|max:20',
                'email'         => 'nullable|string|min:0|max:35',
                'country_id'    => 'nullable|integer|exists:countries,id',
                'city_id'       => 'nullable|integer|exists:cities,id',
                'region_id'     => 'nullable|integer|exists:regions,id', 
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $old_value = ReferralClinic::SELECT('id', 'name', 'address', 'country_id', 'region_id', 'city_id', 'phone_no', 'mobile_no', 'email')
            ->where(['id' => $request->clinic_id, 'is_active' => 1])->get();

            $clinic = ReferralClinic::find($request->clinic_id);
            $clinic->name              = $request->name;
            $clinic->address           = $request->address;
            $clinic->phone_no          = $request->phone_no;
            $clinic->mobile_no         = $request->mobile_no;
            $clinic->email             = $request->email;
            $clinic->country_id        = $request->country_id;
            $clinic->region_id         = $request->region_id;
            $clinic->city_id           = $request->city_id;
            $clinic->updated_by = $request->user_id;
            $clinic->updated_at = date('Y-m-d H:i:s');
            $clinic->update();

            if($clinic->id) {
                $field_names = [
                    'name' => 'Clinic name updated', 
                    'address' => 'Address details updated', 
                    'phone_no' => 'Phone number updated', 
                    'mobile_no' => 'Mobile number updated', 
                    'email' => 'Email ID updated', 
                    'country_id' => 'Country name updated', 
                    'region_id' => 'Region name updated', 
                    'city_id' => 'City name updated'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $clinic->id, 'MasterLog', 'ReferralClinic', $old_value, $clinic, $field_names);

                return $this->sendResponse(1,200, 'Clinic updated successfully', 'clinic_id', $clinic->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdateReferralClinic:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteClinic(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'clinic_id' => 'required|integer|exists:referral_clinics,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $clinic = ReferralClinic::find($request->clinic_id);
            $clinic->is_active = 0;
            $clinic->updated_by = $request->user_id;
            $clinic->updated_at = date('Y-m-d H:i:s');
            $clinic->update();

            if($clinic->id) {
                $delete_logs = $this->DeleteLogs($request->user_id, $clinic->id, 'MasterLog', 'ReferralClinic', 'Description');
                return $this->sendResponse(1,200, 'Clinic deleted successfully', 'clinic_id', $clinic->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteClinic:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //Enquiry Reason 
    public function EnquiryReasonList(Request $request) {
        try {

            $reason = EnquiryReason::SELECT('id', 'name')
                        ->where(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $reason);
            
        } catch(\Exception $e) {
            Log::debug("API EnquiryReasonList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function AddEnquiryReason(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'name'          => 'required|string|min:3|max:50',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $reason = EnquiryReason::create([
                'name' => $request->name,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($reason->id) {
                return $this->sendResponse(1,200, 'Reason added successfully', 'reason_id', $reason->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddEnquiryReason:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleEnquiryReason($id) {
        try {
            $reason = EnquiryReason::SELECT('id', 'name')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($reason->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $reason);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $reason->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleEnquiryReason :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateEnquiryReason(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'reason_id'     => 'required|integer|exists:enquiry_reasons,id,is_active,1',
                'name'          => 'required|string|min:3|max:30',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

        $old_value = EnquiryReason::SELECT('id', 'name')
                ->where(['id' => $request->reason_id, 'is_active' => 1])->get();

            $reason = EnquiryReason::find($request->reason_id);
            $reason->name              = $request->name;
            $reason->updated_by = $request->user_id;
            $reason->updated_at = date('Y-m-d H:i:s');
            $reason->update();

            if($reason->id) {
                $field_names = [
                     'name' => 'Reason name updated'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $reason->id, 'MasterLog', 'EnquiryReason', $old_value, $reason, $field_names);

                return $this->sendResponse(1,200, 'Reason updated successfully', 'reason_id', $reason->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdateEnquiryReason:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteReason(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'reason_id' => 'required|integer|exists:enquiry_reasons,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $reason = EnquiryReason::find($request->reason_id);
            $reason->is_active = 0;
            $reason->updated_by = $request->user_id;
            $reason->updated_at = date('Y-m-d H:i:s');
            $reason->update();

            if($reason->id) {
                $delete_logs = $this->DeleteLogs($request->user_id, $reason->id, 'MasterLog', 'EnquiryReason', 'Description');
                return $this->sendResponse(1,200, 'Reason deleted successfully', 'reason_id', $reason->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteReason:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //Enquiry services 
    public function EnquiryServiceList(Request $request) {
        try {

            $reason = EnquiryService::SELECT('id', 'name')
                        ->where(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $reason);
            
        } catch(\Exception $e) {
            Log::debug("API EnquiryServiceList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function AddEnquiryService(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'name'          => 'required|string|min:3|max:50',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $service = EnquiryService::create([
                'name' => $request->name,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($service->id) {
                return $this->sendResponse(1,200, 'Service added successfully', 'service_id', $service->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddEnquiryservice:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleEnquiryService($id) {
        try {
            $service = EnquiryService::SELECT('id', 'name')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($service->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $service);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $service->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleEnquiryService :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateEnquiryService(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'service_id'     => 'required|integer|exists:enquiry_services,id,is_active,1',
                'name'          => 'required|string|min:3|max:30',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $service = EnquiryService::find($request->service_id);
            $service->name              = $request->name;
            $service->updated_by = $request->user_id;
            $service->updated_at = date('Y-m-d H:i:s');
            $service->update();

            if($service->id) {
                return $this->sendResponse(1,200, 'Service updated successfully', 'service_id', $service->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdateEnquiryService:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteService(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'service_id' => 'required|integer|exists:enquiry_services,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $service = EnquiryService::find($request->service_id);
            $service->is_active = 0;
            $service->updated_by = $request->user_id;
            $service->updated_at = date('Y-m-d H:i:s');
            $service->update();

            if($service->id) {
                return $this->sendResponse(1,200, 'Service deleted successfully', 'service_id', $service->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteService:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //Insurance Network
    public function AddNetwork(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'                       => 'required|integer|exists:users,id,is_active,1',
                'insurance_company_detail_id'   => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'network_type'                  => 'required|string|min:3|max:50',
                'is_status'                     => 'required|integer|in:1,0',
                'name'                          => 'required|string|min:3|max:50',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $record = InsuranceNetwork::where(['name' => $request->name, 'insurance_company_detail_id'=>$request->insurance_company_detail_id, 'is_active'=> 1])->get();
            if(count($record) != 0) {
                return $this->sendResponse(0,200, 'Network name already exist');
            }
            $network = InsuranceNetwork::create([
                'name' => $request->name,
                'insurance_company_detail_id' => $request->insurance_company_detail_id,
                'network_type' => $request->network_type,
                'is_status' => $request->is_status,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($network->id) {
                return $this->sendResponse(1,200, 'Network added successfully', 'network_id', $network->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddNetwork:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleNetwork($id) {
        try {
            $network = InsuranceNetwork::SELECT('id as network_id', 'name', 'insurance_company_detail_id', 'network_type', 'is_status')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($network->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $network);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $network->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleNetwork :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateNetwork(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'network_id'                    => 'required|integer|exists:insurance_networks,id,is_active,1',
                'user_id'                       => 'required|integer|exists:users,id,is_active,1',
                'insurance_company_detail_id'   => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'network_type'                  => 'required|string|min:3|max:50',
                'is_status'                     => 'required|integer|in:1,0',
                'name'                          => 'required|string|min:3|max:50',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }
            $old_value = InsuranceNetwork::where(['id' => $request->network_id, 'is_active'=>1])->get();
            $network = InsuranceNetwork::find($request->network_id);
            $network->name                    = $request->name;
            $network->insurance_company_detail_id  = $request->insurance_company_detail_id;
            $network->network_type           = $request->network_type;
            $network->is_status              = $request->is_status;
            $network->updated_by = $request->user_id;
            $network->updated_at = date('Y-m-d H:i:s');
            $network->update();

            if($network->id) {
                $field_names = [
                    'name' => 'Network name updated', 
                    'insurance_company_detail_id' => 'Insurance company changed', 
                    'network_type' => 'Network type  updated', 
                    'is_status' => 'Status changed'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $request->network_id, 'MasterLog', 'InsuranceNetwork', $old_value, $network, $field_names);
                return $this->sendResponse(1,200, 'Network updated successfully', 'network_id', $network->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdateNetwork:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteNetwork(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'network_id' => 'required|integer|exists:insurance_networks,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $network = InsuranceNetwork::find($request->network_id);
            $network->is_active = 0;
            $network->updated_by = $request->user_id;
            $network->updated_at = date('Y-m-d H:i:s');
            $network->update();

            if($network->id) {
                $delete_logs = $this->DeleteLogs($request->user_id, $network->id, 'MasterLog', 'InsuranceNetwork', 'Description');
                return $this->sendResponse(1,200, 'Network deleted successfully', 'network_id', $network->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteNetwork:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetNetworkInsCompanyWise($id) {
        try {
            $network = InsuranceNetwork::SELECT('id', 'name', 'network_type')
                ->where(['insurance_company_detail_id' => $id, 'is_status'=> 1, 'is_active' => 1])->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $network);
            
        } catch(\Exception $e) {
            Log::debug('API GetNetworkInsCompanyWise :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function ViewNetworkList(Request $request) {
        try {

            $networks = ViewInsuranceNetwork::SELECT('network_id', 'name', 'network_type', 'insurance_company_name', 'is_status', 'insurance_company_detail_id')
            ->ORDERBY('name', 'ASC')->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $networks);
            
        } catch(\Exception $e) {
            Log::debug('API ViewNetworkList :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }



    //Insurance Package
    public function AddInsPackage(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'                       => 'required|integer|exists:users,id,is_active,1',
                'insurance_company_detail_id'   => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'product_name'                  => 'nullable|string|min:3|max:100',
                'payer_ids'                     => 'nullable|string|min:2|max:30',
                'name'                          => 'required|string|min:3|max:30',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $record = InsurancePackage::where(['name' => $request->name, 'insurance_company_detail_id'=>$request->insurance_company_detail_id, 'is_active'=> 1])->get();
            if(count($record) != 0) {
                return $this->sendResponse(0,200, 'Package name already exist');
            }
            $package = InsurancePackage::create([
                'name' => $request->name,
                'insurance_company_detail_id' => $request->insurance_company_detail_id,
                'product_name' => $request->product_name,
                'payer_ids' => $request->payer_ids,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($package->id) {
                return $this->sendResponse(1,200, 'Insurance Package added successfully', 'package_id', $package->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddInsPackage:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleInsPackage($id) {
        try {
            $network = ViewInsurancePackage::SELECT('package_id', 'name', 'insurance_company_detail_id', 'insurance_company_name', 'product_name', 'payer_ids')
                ->where(['package_id' => $id])->get();
            if($network->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $network);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $network->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleInsPackage :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateInsPackage(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'                       => 'required|integer|exists:users,id,is_active,1',
                'package_id'                    => 'required|integer|exists:insurance_packages,id,is_active,1',
                'insurance_company_detail_id'   => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'product_name'                  => 'nullable|string|min:3|max:100',
                'payer_ids'                     => 'nullable|string|min:2|max:30',
                'name'                          => 'required|string|min:3|max:30',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }
            $old_value = InsurancePackage::where(['id' => $request->package_id, 'is_active'=>1])->get();
            $package = InsurancePackage::find($request->package_id);
            $package->name                    = $request->name;
            $package->insurance_company_detail_id  = $request->insurance_company_detail_id;
            $package->product_name           = $request->product_name;
            $package->payer_ids              = $request->payer_ids;
            $package->updated_by = $request->user_id;
            $package->updated_at = date('Y-m-d H:i:s');
            $package->update();

            if($package->id) {
                $field_names = [
                    'name' => 'package name updated', 
                    'insurance_company_detail_id' => 'Insurance company changed', 
                    'product_name' => 'product name updated', 
                    'payer_ids' => 'Payer ID updated'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $request->package_id, 'MasterLog', 'InsurancePackage', $old_value, $package, $field_names);
                return $this->sendResponse(1,200, 'Insurance Package updated successfully', 'network_id', $package->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdateInsPackage:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteInsPackage(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'package_id' => 'required|integer|exists:insurance_packages,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $package = InsurancePackage::find($request->package_id);
            $package->is_active = 0;
            $package->updated_by = $request->user_id;
            $package->updated_at = date('Y-m-d H:i:s');
            $package->update();

            if($package->id) {
                $delete_logs = $this->DeleteLogs($request->user_id, $package->id, 'MasterLog', 'InsurancePackage', 'Description');
                return $this->sendResponse(1,200, 'Insurance Package deleted successfully', 'package_id', $package->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteInsPackage:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetPackageInsCompanyWise($id) {
        try {
            $package = ViewInsurancePackage::SELECT('package_id', 'name')
                ->where(['insurance_company_detail_id' => $id])->ORDERBY('name', 'ASC')->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $package);
            
        } catch(\Exception $e) {
            Log::debug('API GetPackageInsCompanyWise :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function ViewInsPackageList(Request $request) {
        try {

            $package =ViewInsurancePackage::SELECT('package_id', 'name', 'product_name', 'payer_ids', 'insurance_company_name', 'insurance_company_detail_id')->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $package);
            
        } catch(\Exception $e) {
            Log::debug('API ViewInsPackageList :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    //Insurance Plans
    public function AddInsPlan(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'                       => 'required|integer|exists:users,id,is_active,1',
                'hospital_detail_id'            => 'required|integer|exists:hospital_details,id,is_active,1',
                'insurance_company_detail_id'   => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'insurance_network_id'          => 'required|integer|exists:insurance_networks,id,is_active,1',
                'plan_name'                     => 'required|string|min:3|max:100',
                'network_separate_price'        => 'required|integer|in:0,1',
                'is_status'                     => 'required|integer|in:0,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $record = InsurancePlan::where(['plan_name' => $request->plan_name, 'insurance_company_detail_id'=>$request->insurance_company_detail_id, 'insurance_network_id'=>$request->insurance_network_id, 'hospital_detail_id'=>$request->hospital_detail_id, 'is_active'=> 1])->get();
            if(count($record) != 0) {
                return $this->sendResponse(0,200, 'Plan name already exist');
            }
            $plans = InsurancePlan::create([
                'ndiff_price' => $request->ndiff_price,
                'hospital_detail_id' => $request->hospital_detail_id,
                'insurance_company_detail_id' => $request->insurance_company_detail_id,
                'insurance_network_id' => $request->insurance_network_id,
                'plan_name' => $request->plan_name,
                'network_separate_price' => $request->network_separate_price,
                'is_status' => $request->is_status,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($plans->id) {
                return $this->sendResponse(1,200, 'Insurance plans added successfully', 'plan_id', $plans->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddInsPlan:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleInsPlan($id) {
        try {
            $plans = ViewInsurancePlan::SELECT('plan_id', 'hospital_detail_id', 'plan_name', 'network_separate_price', 'insurance_company_detail_id', 'insurance_network_id', 'insurance_company_name', 'network_name', 'is_status')
                ->where(['plan_id' => $id])->get();
            if($plans->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $plans);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $plans->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleInsPlan :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateInsPlan(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'                       => 'required|integer|exists:users,id,is_active,1',
                'plan_id'                       => 'required|integer|exists:insurance_plans,id,is_active,1',
                'hospital_detail_id'            => 'required|integer|exists:hospital_details,id,is_active,1',
                'insurance_company_detail_id'   => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'insurance_network_id'          => 'required|integer|exists:insurance_networks,id,is_active,1',
                'plan_name'                     => 'required|string|min:3|max:100',
                'network_separate_price'        => 'required|integer|in:0,1',
                'is_status'                     => 'required|integer|in:0,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }
            $old_value = ViewInsurancePlan::where(['plan_id' => $request->plan_id])->get();
            $plans = InsurancePlan::find($request->plan_id);
            $plans->hospital_detail_id          = $request->hospital_detail_id;
            $plans->plan_name                    = $request->plan_name;
            $plans->insurance_company_detail_id  = $request->insurance_company_detail_id;
            $plans->insurance_network_id        = $request->insurance_network_id;
            $plans->network_separate_price      = $request->network_separate_price;
            $plans->is_status                   = $request->is_status;
            $plans->updated_by = $request->user_id;
            $plans->updated_at = date('Y-m-d H:i:s');
            $plans->update();

            if($plans->id) {
                $field_names = [
                    'hospital_detail_id' => 'Location changed',
                    'plan_name' => 'plan name updated', 
                    'insurance_company_detail_id' => 'Insurance company changed', 
                    'insurance_network_id' => 'insurance_network changed', 
                    'network_separate_price' => 'Network separate price updated',
                    'is_status'         => 'Status updated'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $request->plan_id, 'MasterLog', 'InsurancePlan', $old_value, $plans, $field_names);
                return $this->sendResponse(1,200, 'Insurance plan updated successfully', 'network_id', $plans->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdateInsPlan:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteInsPlan(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'plan_id' => 'required|integer|exists:insurance_plans,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $plans = InsurancePlan::find($request->plan_id);
            $plans->is_active = 0;
            $plans->updated_by = $request->user_id;
            $plans->updated_at = date('Y-m-d H:i:s');
            $plans->update();

            if($plans->id) {
                $delete_logs = $this->DeleteLogs($request->user_id, $plans->id, 'MasterLog', 'InsurancePlan', 'Description');
                return $this->sendResponse(1,200, 'Insurance Plan deleted successfully', 'plan_id', $plans->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteInsPlan:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetPlanNetworkWise($id) {
        try {
            $package = ViewInsurancePlan::SELECT('plan_id', 'plan_name')
                ->where(['insurance_network_id' => $id])->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $package);
            
        } catch(\Exception $e) {
            Log::debug('API GetPlanNetworkWise :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function ViewInsPlanList(Request $request) {
        try {

            $plans =ViewInsurancePlan::SELECT('plan_id', 'hospital_detail_id', 'plan_name', 'network_separate_price', 'insurance_company_detail_id', 'insurance_network_id', 'insurance_company_name', 'network_name', 'is_status')
                ->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $plans);
            
        } catch(\Exception $e) {
            Log::debug('API ViewInsPlanList :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }



    //Appointment Status
    public function AddAppointmentStatus(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'               => 'required|integer|exists:users,id,is_active,1',
                'name'                  => 'required|string|min:3|max:100',
                'bg_color'              => 'required|string|min:6|max:20',
                'font_color'            => 'required|string|min:6|max:20',
                'malaffi_status'        => 'nullable|string|min:3|max:100',
                'malaffi_description'   => 'nullable|string|min:1|max:250',
                'booking_order'         => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $record = AppointmentStatus::where(['name' => $request->name, 'is_active'=> 1])->get();
            if(count($record) != 0) {
                return $this->sendResponse(0,200, 'Appointment Status name already exist');
            }
            $plans = AppointmentStatus::create([
                'name' => $request->name,
                'bg_color' => $request->bg_color,
                'font_color' => $request->font_color,
                'malaffi_status' => $request->malaffi_status,
                'malaffi_description' => $request->malaffi_description,
                'booking_order' => $request->booking_order,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($plans->id) {
                return $this->sendResponse(1,200, 'Appointment Status added successfully', 'plan_id', $plans->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddAppointmentStatus:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleAppointmentStatus($id) {
        try {
            $status = AppointmentStatus::SELECT('id', 'name', 'bg_color', 'font_color', 'malaffi_status', 'malaffi_description', 'booking_order')
                ->where(['id' => $id, 'is_active'=>1])->get();
            if($status->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $status);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $status->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleAppointmentStatus :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateAppointmentStatus(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'               => 'required|integer|exists:users,id,is_active,1',
                'status_id'             => 'required|integer|exists:appointment_statuses,id,is_active,1',
                'name'                  => 'required|string|min:3|max:100',
                'bg_color'              => 'required|string|min:6|max:20',
                'font_color'            => 'required|string|min:6|max:20',
                'malaffi_status'        => 'nullable|string|min:3|max:100',
                'malaffi_description'   => 'nullable|string|min:1|max:250',
                'booking_order'         => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }
            $old_value = AppointmentStatus::where(['id' => $request->status_id])->get();
            $status = AppointmentStatus::find($request->status_id);
            $status->name                    = $request->name;
            $status->bg_color                = $request->bg_color;
            $status->font_color              = $request->font_color;
            $status->malaffi_status                = $request->malaffi_status;
            $status->malaffi_description           = $request->malaffi_description;
            $status->booking_order                 = $request->booking_order;
            $status->updated_by = $request->user_id;
            $status->updated_at = date('Y-m-d H:i:s');
            $status->update();

            if($status->id) {
                $field_names = [
                    'name' => 'status name updated', 
                    'bg_color' => 'Background color changed', 
                    'font_color' => 'font color changed', 
                    'malaffi_status' => 'malaffi status updated',
                    'malaffi_description' => 'malaffi description updated', 
                    'booking_order' => 'booking order changed'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $request->status_id, 'MasterLog', 'AppointmentStatus', $old_value, $status, $field_names);
                return $this->sendResponse(1,200, 'Appointment Status updated successfully', 'status_id', $status->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdateAppointmentStatus:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteAppointmentStatus(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'status_id' => 'required|integer|exists:appointment_statuses,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $status = AppointmentStatus::find($request->status_id);
            $status->is_active = 0;
            $status->updated_by = $request->user_id;
            $status->updated_at = date('Y-m-d H:i:s');
            $status->update();

            if($status->id) {
                $delete_logs = $this->DeleteLogs($request->user_id, $status->id, 'MasterLog', 'AppointmentStatus', 'Description');
                return $this->sendResponse(1,200, 'Appointment Status deleted successfully', 'status_id', $status->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteAppointmentStatus:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetAppointmentStatusList(Request $request) {
        try {
            $status = AppointmentStatus::SELECT('id', 'name', 'bg_color', 'font_color')
                ->where(['is_active' => 1])->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $status);
            
        } catch(\Exception $e) {
            Log::debug('API GetAppointmentStatusList :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function ViewAppointmentStatus(Request $request) {
        try {

            $status =AppointmentStatus::SELECT('id', 'name', 'bg_color', 'font_color', 'malaffi_status', 'malaffi_description', 'booking_order')->where(['is_active'=>1])->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $status);
            
        } catch(\Exception $e) {
            Log::debug('API ViewAppointmentStatus :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    //SMS Template
    public function AddSMSTemplate(Request $request) {
        try {

            Validator::extend('without_spaces', function($attr, $value){
                return preg_match('/^\S*$/u', $value);
            });

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'name'              => 'required|string|min:3|max:100',
                'short_code'        => 'required|string|without_spaces|min:4|max:20',
                'english_message'   => 'required|string',
                'arabic_message'    => 'nullable|string'
            ],[
                'short_code.without_spaces' => 'Short code whitespace not allowed.'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $record = SmsTemplate::where(['short_code' => $request->short_code, 'is_active'=> 1])->get();
            if(count($record) != 0) {
                return $this->sendResponse(0,200, 'SMS Short code already exist');
            }
            $sms = SmsTemplate::create([
                'name' => $request->name,
                'short_code' => $request->short_code,
                'english_message' => $request->english_message,
                'arabic_message' => $request->arabic_message,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($sms->id) {
                return $this->sendResponse(1,200, 'SMS Template added successfully', 'sms_id', $sms->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddSMSTemplate:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleSMSTemplate($id) {
        try {
            $sms = SmsTemplate::SELECT('id', 'name', 'short_code', 'english_message', 'arabic_message')
                ->where(['id' => $id, 'is_active'=>1])->get();
            if($sms->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $sms);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $sms->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleSMSTemplate :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateSMSTemplate(Request $request) {
        try {
            Validator::extend('without_spaces', function($attr, $value){
                return preg_match('/^\S*$/u', $value);
            });

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'sms_id'            => 'required|integer|exists:sms_templates,id,is_active,1',
                'name'              => 'required|string|min:3|max:100',
                'short_code'        => 'required|string|without_spaces|min:4|max:20',
                'english_message'   => 'required|string',
                'arabic_message'    => 'nullable|string'
            ],[
                'short_code.without_spaces' => 'Short code whitespace not allowed.'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }
            $old_value = SmsTemplate::where(['id' => $request->sms_id])->get();
            $sms = SmsTemplate::find($request->sms_id);
            $sms->name                = $request->name;
            $sms->short_code          = $request->short_code;
            $sms->english_message     = $request->english_message;
            $sms->arabic_message      = $request->arabic_message;
            $sms->updated_by = $request->user_id;
            $sms->updated_at = date('Y-m-d H:i:s');
            $sms->update();

            if($sms->id) {
                $field_names = [
                    'name' => 'SMS Template name updated',
                    'short_code' => 'SMS template Short code changed', 
                    'arabic_message' => 'Arabic message updated',
                    'english_message' => 'English message updated'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $request->sms_id, 'MasterLog', 'SmsTemplate', $old_value, $sms, $field_names);
                return $this->sendResponse(1,200, 'SMS Template updated successfully', 'sms_id', $sms->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdateSMSTemplate:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteSmsTemplate(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'sms_id' => 'required|integer|exists:sms_templates,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $sms = SmsTemplate::find($request->sms_id);
            $sms->is_active = 0;
            $sms->updated_by = $request->user_id;
            $sms->updated_at = date('Y-m-d H:i:s');
            $sms->update();

            if($sms->id) {
                $delete_logs = $this->DeleteLogs($request->user_id, $sms->id, 'MasterLog', 'SmsTemplate', 'Description');
                return $this->sendResponse(1,200, 'SMS Template deleted successfully', 'sms_id', $sms->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteSmsTemplate:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function ViewSmsTemplate(Request $request) {
        try {

            $sms = SmsTemplate::SELECT('id', 'name', 'short_code', 'english_message', 'arabic_message')
                    ->where(['is_active'=>1])->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $sms);
            
        } catch(\Exception $e) {
            Log::debug('API ViewSmsTemplate :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


     //Insurance Plan Detail
     public function AddInsPlanDetail(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'                       => 'required|integer|exists:users,id,is_active,1',
                'hospital_detail_id'            => 'required|integer|exists:hospital_details,id,is_active,1',
                'insurance_company_detail_id'   => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'insurance_network_id'          => 'required|integer|exists:insurance_networks,id,is_active,1',
                'insurance_plan_id'             => 'required|integer|exists:insurance_plans,id,is_active,1',
                //'plan_require_approval'         => 'required|integer|in:0,1',
                'before_discount'               => 'required|integer|in:0,1',
                'after_discount'                => 'required|integer|in:0,1',
                'validity_approve_days'         => 'nullable|integer',
                'limit_per_invoice'             => 'nullable|integer|min:0|max:10000000',
                //'discontinue_network'           => 'required|integer|in:0,1',
                //'discontinue_plan'              => 'required|integer|in:0,1',
                'free_followup_days'            => 'nullable|integer',
                'max_ceiling'                   => 'nullable|integer|min:0|max:10000000',
                'co_insurance_exist_patient'    => 'required|integer|in:0,1',
                'deduct_exist_patient'          => 'required|integer|in:0,1',
                'discount_all_network'          => 'required|integer|in:0,1',
                'discount_all_plan'             => 'required|integer|in:0,1',
                'factor_all_network'            => 'required|integer|in:0,1',
                'factor_all_plans'              => 'required|integer|in:0,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            $descriptions = $request->description;
            if(empty($descriptions)) {
                return $this->sendResponse(0,200, 'Department category is required');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $record = PlanDetail::where(['hospital_detail_id'=>$request->hospital_detail_id, 'insurance_plan_id' => $request->insurance_plan_id, 'insurance_company_detail_id'=>$request->insurance_company_detail_id, 'insurance_network_id'=>$request->insurance_network_id,  'is_active'=> 1])->get();
            if(count($record) != 0) {
                return $this->sendResponse(0,200, 'Plan detail already exist');
            }

            $plans = PlanDetail::create([
                'hospital_detail_id' => $request->hospital_detail_id,
                'insurance_company_detail_id' => $request->insurance_company_detail_id,
                'insurance_network_id' => $request->insurance_network_id,
                'insurance_plan_id' => $request->insurance_plan_id,
                //'plan_require_approval' => $request->plan_require_approval,
                'before_discount' => $request->before_discount,
                'after_discount' => $request->after_discount,
                'validity_approve_days' => $request->validity_approve_days,
                'limit_per_invoice' => $request->limit_per_invoice,
                'discontinue_network' => $request->discontinue_network,
                'discontinue_plan' => $request->discontinue_plan,
                'free_followup_days' => $request->free_followup_days,
                'max_ceiling' => $request->max_ceiling,
                'co_insurance_exist_patient' => $request->co_insurance_exist_patient,
                'deduct_exist_patient' => $request->deduct_exist_patient,
                //'discount_all_network' => $request->discount_all_network,
                //'discount_all_plan' => $request->discount_all_plan,
                'factor_all_network' => $request->factor_all_network,
                'factor_all_plans' => $request->factor_all_plans,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($plans->id) {
                foreach($descriptions as $desc) {
                    $detail = PlanDetailDescription::create([
                        'plan_detail_id' => $plans->id,
                        'dept_category_id' => $desc['dept_category_id'],
                        'department_id' => $desc['department_id'],
                        'out_patient' => $desc['out_patient'],
                        'out_patient_discount' => $desc['out_patient_discount'],
                        'in_patient' => $desc['in_patient'],
                        'in_patient_discount' => $desc['in_patient_discount'],
                        'co_ins_ongross' => $desc['co_ins_ongross'],
                        'co_ins_onnet' => $desc['co_ins_onnet'],
                        'co_pay_percentage' => $desc['co_pay_percentage'],
                        'dedcut_amount' => $desc['dedcut_amount'],
                        'per_request' => $desc['per_request'],
                        'factor' => $desc['factor'],
                        'bill_exceeds' => $desc['bill_exceeds'],
                        'sort_by' => $desc['sort_by'],
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                return $this->sendResponse(1,200, 'Plan detail added successfully', 'plan_detail_id', $plans->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddInsPlanDetail:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleInsPlanDetail($id) {
        try {
            $plans = PlanDetail::SELECT('id', 'hospital_detail_id', 'insurance_company_detail_id', 'insurance_network_id', 'insurance_plan_id', 'plan_require_approval','before_discount','after_discount','validity_approve_days','limit_per_invoice','discontinue_network','discontinue_plan','free_followup_days','max_ceiling','co_insurance_exist_patient','deduct_exist_patient','discount_all_network','discount_all_plan','factor_all_network','factor_all_plans')->where(['id' => $id, 'is_active' => 1])->get();
            if($plans->count() == 1) {
                $category = DB::select("SELECT GROUP_CONCAT(DISTINCT(dept_category_id)) as 'category_ids'  FROM plan_detail_descriptions WHERE plan_detail_id = $id AND is_active = 1");
                $ids = explode(',',$category[0]->category_ids);
                $categories = DeptCategory::SELECT('id', 'name')
                    ->WHEREIN('id', $ids)
                    ->WHERE(['is_active' => 1])
                    ->ORDERBY('name', 'ASC')->get();
                $response = [];
                $result = array();
                if($categories->count() != 0) {
                    foreach($categories as $cat) {
                        $cat_id = $cat->id;
                        $dept = PlanDetailDescription::SELECT('id as desc_id', 'dept_category_id', 'department_id','plan_detail_id','out_patient','out_patient_discount','in_patient', 'in_patient_discount','co_ins_ongross','co_ins_onnet','co_pay_percentage', 'dedcut_amount', 'per_request', 'factor', 'sort_by', 'bill_exceeds')
                                ->WHERE(['plan_detail_id'=>$id, 'dept_category_id' => $cat_id, 'is_active' => 1])->get();

                        $result[] = [
                            'category_name'=>$cat->name, 
                            'category_id'=>$cat->id,
                            'department' =>$dept
                        ]; 
                        
                    }
                    $response['plan'] = $plans;
                    $response['category_ids'] = $ids;
                    $response['description'] = $result;
                    return $this->sendResponse(1, 200, 'Success', 'data', $response);
                } else {
                    $response['plan'] = $plans;
                    $response['description'] = [];
                    return $this->sendResponse(1, 200, 'Success', 'data', $response);
                }
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $plans->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleInsPlanDetail :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateInsPlanDetail(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'                       => 'required|integer|exists:users,id,is_active,1',
                'plan_detail_id'                => 'required|integer|exists:plan_details,id,is_active,1',
                'hospital_detail_id'            => 'required|integer|exists:hospital_details,id,is_active,1',
                'insurance_company_detail_id'   => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'insurance_network_id'          => 'required|integer|exists:insurance_networks,id,is_active,1',
                'insurance_plan_id'             => 'required|integer|exists:insurance_plans,id,is_active,1',
                //'plan_require_approval'         => 'required|integer|in:0,1',
                'before_discount'               => 'required|integer|in:0,1',
                'after_discount'                => 'required|integer|in:0,1',
                'validity_approve_days'         => 'nullable|integer',
                'limit_per_invoice'             => 'nullable|integer|min:0|max:10000000',
                'discontinue_network'           => 'required|integer|in:0,1',
                'discontinue_plan'              => 'required|integer|in:0,1',
                'free_followup_days'            => 'nullable|integer',
                'max_ceiling'                   => 'nullable|integer|min:0|max:10000000',
                'co_insurance_exist_patient'    => 'required|integer|in:0,1',
                'deduct_exist_patient'          => 'required|integer|in:0,1',
                //'discount_all_network'          => 'required|integer|in:0,1',
                //'discount_all_plan'             => 'required|integer|in:0,1',
                'factor_all_network'            => 'required|integer|in:0,1',
                'factor_all_plans'              => 'required|integer|in:0,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            $descriptions = $request->description;
            if(empty($descriptions)) {
                return $this->sendResponse(0,200, 'Department category is required');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $old_value = PlanDetail::where(['id' => $request->plan_detail_id])->get();
            $plans = PlanDetail::find($request->plan_detail_id);
            $plans->hospital_detail_id          = $request->hospital_detail_id;
            $plans->insurance_plan_id           = $request->insurance_plan_id;
            $plans->insurance_company_detail_id  = $request->insurance_company_detail_id;
            $plans->insurance_network_id        = $request->insurance_network_id;
            //$plans->plan_require_approval   = $request->plan_require_approval;
            $plans->before_discount         = $request->before_discount;
            $plans->after_discount          = $request->after_discount;
            $plans->validity_approve_days   = $request->validity_approve_days;
            $plans->limit_per_invoice       = $request->limit_per_invoice;
            //$plans->discontinue_network     = $request->discontinue_network;
            //$plans->discontinue_plan        = $request->discontinue_plan;
            $plans->free_followup_days      = $request->free_followup_days;
            $plans->max_ceiling             = $request->max_ceiling;
            $plans->co_insurance_exist_patient = $request->co_insurance_exist_patient;
            $plans->deduct_exist_patient    = $request->deduct_exist_patient;
            $plans->discount_all_network    = $request->discount_all_network;
            $plans->discount_all_plan   = $request->discount_all_plan;
            $plans->factor_all_network  = $request->factor_all_network;
            $plans->factor_all_plans    = $request->factor_all_plans;
            $plans->updated_by = $request->user_id;
            $plans->updated_at = date('Y-m-d H:i:s');
            $plans->update();

            if($plans->id) {
                $field_names = [
                    'hospital_detail_id' => 'Location changed',
                    'insurance_network_id' => 'Plan changed', 
                    'insurance_company_detail_id' => 'Insurance company changed', 
                    'insurance_network_id' => 'insurance_network changed', 
                    'plan_require_approval' => 'Plan require approval updated',
                    'before_discount' => 'Before discount updated', 
                    'after_discount' => 'After discount updated', 
                    'validity_approve_days' => 'validity approve days updated', 
                    'limit_per_invoice' => 'Limit per invoice updated',
                    'discontinue_network' => 'Discontinue this network updated',
                    'discontinue_plan' => 'Discontinue this plan updated', 
                    'free_followup_days' => 'Free followup within days updated', 
                    'max_ceiling' => 'Max ceiling amount updated', 
                    'co_insurance_exist_patient' => 'Co-Insurance for all existing patients under this plan updated',
                    'deduct_exist_patient' => 'Deductible for all existing patients under this plan updated',
                    'discount_all_network' => 'Same discount for all networks updated', 
                    'discount_all_plan' => 'Same discount for all plans updated', 
                    'factor_all_network' => 'Same factor for all networks updated', 
                    'factor_all_plans' => 'Same factor for all plans updated'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $request->plan_detail_id, 'MasterLog', 'PlanDetail', $old_value, $plans, $field_names);

                $field_names1 = [
                    'dept_category_id' => 'Department category changed',
                    'department_id' => 'Department changed',
                    'out_patient' => 'Out patient updated', 
                    'out_patient_discount' => 'Out patient discount updated', 
                    'in_patient' => 'In patient updated', 
                    'in_patient_discount' => 'In patient discount updated',
                    'co_ins_ongross' => 'Co Insurance ongross updated', 
                    'co_ins_onnet' => 'Co Insuranc OnNet updated', 
                    'co_pay_percentage' => 'Co-Pay percentage updated', 
                    'dedcut_amount' => 'Dedcut amount updated',
                    'per_request' => 'Per Request updated', 
                    'factor' => 'Multi factor updated', 
                    'bill_exceeds' => 'Bill exceeds updated', 
                    'sort_by' => 'Sort by updated'
                ];
                foreach($descriptions as $desc) {
                    if($desc['desc_id'] != 0) {
                        $desc_id = $desc['desc_id'];
                        $desc_value = PlanDetailDescription::where(['id' => $desc_id])->get();
                        $up_desc = PlanDetailDescription::find($desc_id);
                        $up_desc->dept_category_id      = $desc['dept_category_id'];
                        $up_desc->department_id         = $desc['department_id'];
                        $up_desc->out_patient           = $desc['out_patient'];
                        $up_desc->out_patient_discount  = $desc['out_patient_discount'];
                        $up_desc->in_patient            = $desc['in_patient'];
                        $up_desc->in_patient_discount   = $desc['in_patient_discount'];
                        $up_desc->co_ins_ongross        = $desc['co_ins_ongross'];
                        $up_desc->co_ins_onnet          = $desc['co_ins_onnet'];
                        $up_desc->co_pay_percentage     = $desc['co_pay_percentage'];
                        $up_desc->dedcut_amount         = $desc['dedcut_amount'];
                        $up_desc->per_request           = $desc['per_request'];
                        $up_desc->factor                = $desc['factor'];
                        $up_desc->bill_exceeds          = $desc['bill_exceeds'];
                        $up_desc->sort_by               = $desc['sort_by'];
                        $up_desc->updated_by = $request->user_id;
                        $up_desc->updated_at = date('Y-m-d H:i:s');
                        $up_desc->update();

                        $update_logs = $this->UpdateLogs($request->user_id, $desc_id, 'MasterLog', 'PlanDetailDescription', $desc_value, $up_desc, $field_names1);
                    } else {
                        $detail = PlanDetailDescription::create([
                            'plan_detail_id' => $plans->id,
                            'dept_category_id' => $desc['dept_category_id'],
                            'department_id' => $desc['department_id'],
                            'out_patient' => $desc['out_patient'],
                            'out_patient_discount' => $desc['out_patient_discount'],
                            'in_patient' => $desc['in_patient'],
                            'in_patient_discount' => $desc['in_patient_discount'],
                            'co_ins_ongross' => $desc['co_ins_ongross'],
                            'co_ins_onnet' => $desc['co_ins_onnet'],
                            'co_pay_percentage' => $desc['co_pay_percentage'],
                            'dedcut_amount' => $desc['dedcut_amount'],
                            'per_request' => $desc['per_request'],
                            'factor' => $desc['factor'],
                            'bill_exceeds' => $desc['bill_exceeds'],
                            'sort_by' => $desc['sort_by'],
                            'created_by' => $request->user_id,
                            'updated_by' => $request->user_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }

                return $this->sendResponse(1,200, 'Plan details updated successfully', 'plan_detail_id', $plans->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdateInsPlanDetail:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function ViewInsPlanDetail(Request $request) {
        try {

            $sms = ViewPlanDetail::SELECT('plan_detail_id', 'center_name', 'insurance_company_name', 'network_name', 'plan_name', 'plan_require_approval','before_discount','after_discount','validity_approve_days','limit_per_invoice','discontinue_network','discontinue_plan','free_followup_days','max_ceiling','co_insurance_exist_patient','deduct_exist_patient','discount_all_network','discount_all_plan','factor_all_network','factor_all_plans')->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $sms);
            
        } catch(\Exception $e) {
            Log::debug('API ViewInsPlanDetail :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteInsPlanDetail(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'plan_detail_id' => 'required|integer|exists:plan_details,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $plans = PlanDetail::find($request->plan_detail_id);
            $plans->is_active = 0;
            $plans->updated_by = $request->user_id;
            $plans->updated_at = date('Y-m-d H:i:s');
            $plans->update();

            if($plans->id) {
                $update = PlanDetailDescription::where(['plan_detail_id' => $request->plan_detail_id])
                    ->update([
                        'is_active'=>0, 
                        'updated_by'=> $request->user_id, 
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                $delete_logs = $this->DeleteLogs($request->user_id, $request->plan_detail_id, 'MasterLog', 'PlanDetail', 'Description');
                return $this->sendResponse(1,200, 'Plan detail deleted successfully', 'plan_detail_id', $plans->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteInsPlanDetail:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeletePlanDesc(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'dept_category_id' => 'required|integer',
                'plan_detail_id' => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $plans = PlanDetailDescription::where(['plan_detail_id' => $request->plan_detail_id, 'dept_category_id' => $request->dept_category_id])->get();

            if($plans->count() != 0) {
                $update = PlanDetailDescription::where(['plan_detail_id' => $request->plan_detail_id, 'dept_category_id' => $request->dept_category_id])
                    ->update([
                        'is_active'=>0, 
                        'updated_by'=> $request->user_id, 
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                $delete_logs = $this->DeleteLogs($request->user_id, $request->plan_detail_id, 'MasterLog', 'PlanDetailDescription', 'Description');
                return $this->sendResponse(1,200, 'Plan Category Removed', 'plan_detail_id', $request->plan_detail_id);
            } else {
                return $this->sendResponse(0,200, 'Record not found');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeletePlanDesc:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetServiceCategory(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'hospital_detail_id' => 'required|integer|exists:hospital_details,id,is_active,1',
                'insurance_company_detail_id' => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'insurance_network_id' => 'required|integer|exists:insurance_networks,id,is_active,1',
                'insurance_plan_id' => 'required|integer|exists:insurance_plans,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $plans = ViewPlanDetail::SELECT('plan_detail_id') 
                    ->WHERE(['hospital_detail_id'=>$request->hospital_detail_id, 'insurance_company_detail_id'=>$request->insurance_company_detail_id, 'insurance_network_id'=>$request->insurance_network_id, 'insurance_plan_id'=>$request->insurance_plan_id])
                    ->get();
            $response = [];
            $result = array();
            if($plans->count() != 0) {
                foreach($plans as $plan) {
                    $plan_detail_id = $plan->plan_detail_id;
                    $plan_detail = ViewPlanDetailDescription::SELECT('category_name', 'department_id','department_name','out_patient','out_patient_discount','in_patient','in_patient_discount','co_ins_ongross','co_ins_onnet','co_pay_percentage','dedcut_amount','per_request','factor','sort_by','bill_exceeds')
                        ->WHERE(['plan_detail_id'=>$plan_detail_id])
                        ->get();
                    if($plan_detail->count() != 0) {
                        $res1[] = [
                            'category'=> $plan_detail[0]->category_name,
                            'category_list' =>$plan_detail
                        ]; 
                    }
                }
                //$category = DeptCategory::SELECT('id',  'name')->WHERE(['id'=>18])->get();
                $department_list = Department::SELECT('id as department_id',  'name as department_name') 
                            ->WHERE(['dept_catgory_id'=>17])->get();
                $response = $res1;
                $res2[] = [
                    'category'=> 'Others',
                    'category_list' =>$department_list
                ]; 
                $resp =[];
                $resp['main_list'] = $response;
                $resp['sub_list'] = $res2;
                return $this->sendResponse(1,200, 'Success', 'data', $resp);
            } else {
                return $this->sendResponse(0,200, 'Service category not found');
            }
            
        } catch(\Exception $e) {
            Log::debug("API GetServiceCategory:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetNetworkPlans(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'hospital_detail_id' => 'required|integer|exists:hospital_details,id,is_active,1',
                'insurance_company_detail_id' => 'required|integer|exists:insurance_company_details,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $response = [];
            $result = [];
            $networks = ViewPlanDetail::SELECT('insurance_network_id', 'network_name')->distinct()
                    ->WHERE(['insurance_company_detail_id' => $request->insurance_company_detail_id, 'hospital_detail_id' => $request->hospital_detail_id])
                    ->get();
            foreach($networks as $network) {
                $network_id = $network->insurance_network_id;
                    $plans = ViewPlanDetail::SELECT('plan_detail_id', 'plan_name')
                            ->WHERE(['insurance_company_detail_id' => $request->insurance_company_detail_id, 'hospital_detail_id' => $request->hospital_detail_id, 'insurance_network_id' => $network_id])
                            ->get();

                $result[] = [
                    'network_id' => $network->insurance_network_id,
                    'network_name' => $network->network_name,
                    'plans'  => $plans
                ];
            }
            $response = $result;
            if(!empty($result))
                return $this->sendResponse(1,200, 'Success', 'data', $response);
            else 
                return $this->sendResponse(0,200, 'Plan not available');

        } catch(\Exception $e) {
            Log::debug("API GetNetworkPlans:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function CopyPlanDetail(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'from_location_id' => 'required|integer|exists:hospital_details,id,is_active,1',
                'from_company_id' => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'to_location_id' => 'required|integer|exists:hospital_details,id,is_active,1',
                'to_company_id' => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'plan_detail_id' => 'required'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue');
            }

            if($request->from_location_id == $request->to_location_id) {
                return $this->sendResponse(0,200, 'Same location not allowed.');
            }

            $response = [];
            $select = PlanDetail::SELECT('id', 'hospital_detail_id', 'insurance_company_detail_id', 'insurance_network_id', 'insurance_plan_id', 'plan_require_approval', 'before_discount', 'after_discount', 'validity_approve_days', 'limit_per_invoice', 'discontinue_network', 'discontinue_plan', 'free_followup_days', 'max_ceiling', 'co_insurance_exist_patient', 'deduct_exist_patient', 'discount_all_network', 'discount_all_plan', 'factor_all_network', 'factor_all_plans')
                ->WHERE(['hospital_detail_id'=>$request->from_location_id, 'insurance_company_detail_id'=>$request->from_company_id, 'is_active'=>1])
                ->WHEREIN('id', $request->plan_detail_id)->get();
            foreach($select as $data) {
                $insurance_network_id  = $data['insurance_network_id'];
                $insurance_plan_id = $data['insurance_plan_id'];
                $is_exist = PlanDetail::WHERE(['hospital_detail_id'=>$request->to_location_id, 'insurance_company_detail_id'=>$request->to_company_id, 'insurance_plan_id'=>$insurance_plan_id, 'insurance_network_id'=>$insurance_network_id, 'is_active'=>1])->get();
                if($is_exist->count() == 0){
                    $response[] = [
                        'hospital_detail_id' => $request->to_location_id, 
                        'insurance_company_detail_id' => $request->to_company_id, 
                        'insurance_network_id' => $data['insurance_network_id'], 
                        'insurance_plan_id' => $data['insurance_plan_id'], 
                        'plan_require_approval' => $data['plan_require_approval'], 
                        'before_discount' => $data['before_discount'], 
                        'after_discount' => $data['after_discount'], 
                        'validity_approve_days' => $data['validity_approve_days'], 
                        'limit_per_invoice' => $data['limit_per_invoice'], 
                        'discontinue_network' => $data['discontinue_network'], 
                        'discontinue_plan' => $data['discontinue_plan'], 
                        'free_followup_days' => $data['free_followup_days'], 
                        'max_ceiling' => $data['max_ceiling'], 
                        'co_insurance_exist_patient' => $data['co_insurance_exist_patient'], 
                        'deduct_exist_patient' => $data['deduct_exist_patient'], 
                        'discount_all_network' => $data['discount_all_network'], 
                        'discount_all_plan' => $data['discount_all_plan'], 
                        'factor_all_network' => $data['factor_all_network'], 
                        'factor_all_plans' => $data['factor_all_plans'],
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
            }

            if(!empty($response)) {
                PlanDetail::insert($response); 
                return $this->sendResponse(1, 200, 'Network Plans copied successfully');
            } else {
                return $this->sendResponse(0, 200, 'Network Plans already exist. Duplicate record not allowed to copy');
            }

        } catch(\Exception $e) {
            Log::debug("API CopyPlanDetail:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //RegComments
     public function AddRegComments(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'patient_detail_id' => 'required|integer|exists:patient_details,id,is_active,1',
                'narrated_by'       => 'required|string|min:4|max:20',
                'comments'          => 'required|string',
                'alertType'         => 'nullable|string',
                'blockAppt'         => 'nullable|in:0,1',
                'doctor_id'         => 'nullable|int',
                'patient_type'      => 'nullable|string',
                'cancel'            => 'nullable|in:0,1',
                'cancelled_at'      => 'nullable|date_format:Y-m-d H:i:s',
                'cancelled_by'      => 'nullable|string'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }
            
            $RegComments= RegComments::create([
                'patient_detail_id' => $request->patient_detail_id,
                'DateTime' => date('Y-m-d H:i:s'),
                'narrated_by' => $request->narrated_by,
                'comments' => $request->comments,
                'alertType' => $request->alertType,
                'blockAppt' => $request->blockAppt,
                'doctor_id' => $request->doctor_id,
                'patient_type' => $request->patient_type,
                'cancel' => $request->cancel,
                'cancelled_at' => $request->cancelled_at,
                'cancelled_by' => $request->cancelled_by,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($RegComments->id) {
                return $this->sendResponse(1,200, 'Comments added successfully', 'regComments_id', $RegComments->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddRegComments:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function UpdateRegComments(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'patient_detail_id' => 'required|integer|exists:patient_details,id,is_active,1',
                'regComments_id'    => 'required|integer|exists:tbl_RegComments,id,is_active,1',
                'narrated_by'       => 'required|string|min:4|max:20',
                'comments'          => 'required|string',
                'alertType'         => 'nullable|string',
                'blockAppt'         => 'nullable|in:0,1',
                'doctor_id'         => 'nullable|int',
                'patient_type'      => 'nullable|string',
                'cancel'            => 'nullable|in:0,1',
                'cancelled_at'      => 'nullable|date_format:Y-m-d H:i:s',
                'cancelled_by'      => 'nullable|int'
            ]);
    
            if ($validator->fails()) {
                Log::debug("Validation errors: " . json_encode($validator->errors()));
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            $authResult = $this->VerifyAuthUser($request->user_id, 0);
            Log::debug("VerifyAuthUser result: " . json_encode($authResult));
            if ($authResult === false) {
                return $this->sendResponse(0, 401, 'Login user token is not matching. Login again and continue', '');
            }
    
            $RegComments = RegComments::where('id', $request->regComments_id)
                                      ->where('is_active', 1)
                                      ->first();
            if (!$RegComments) {
                return $this->sendResponse(0, 200, 'Comment record not found or inactive.', '');
            }
    
            $RegComments->patient_detail_id = $request->patient_detail_id;
            $RegComments->DateTime = date('Y-m-d H:i:s');
            $RegComments->narrated_by = $request->narrated_by;
            $RegComments->comments = $request->comments;
            $RegComments->alertType = $request->alertType;
            $RegComments->blockAppt = $request->blockAppt;
            $RegComments->doctor_id = $request->doctor_id;
            $RegComments->patient_type = $request->patient_type;
            $RegComments->cancel = $request->cancel;
            $RegComments->cancelled_at = $request->cancelled_at;
            $RegComments->cancelled_by = $request->cancelled_by;
            $RegComments->updated_by = $request->user_id;
            $RegComments->updated_at = date('Y-m-d H:i:s');
            $RegComments->save();
    
            return $this->sendResponse(1, 200, 'Comments updated successfully', 'regComments_id', $RegComments->id);
        } catch (\Exception $e) {
            Log::debug("API UpdateRegComments:: " . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function GetRegComments(Request $request) {
        try {
            
            $RegComments = ViewRegComments::SELECT('id', 'doctor_id', 'patient_detail_id', 'DateTime', 'narrated_by', 'comments', 'alertType', 'blockAppt', 'patient_type', 'cancel', 'cancelled_at', 'cancelled_by', 'patient_name', 'doctor_name', 'cancelled_userName')
                        ->ORDERBY('id', 'ASC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $RegComments);
            
        } catch(\Exception $e) {
            Log::debug("API GetRegComments:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function GetSingleRegComments($id) {
        try {
            $RegComments = ViewRegComments::SELECT('id', 'doctor_id', 'patient_detail_id', 'DateTime', 'narrated_by', 'comments', 'alertType', 'blockAppt', 'patient_type', 'cancel', 'cancelled_at', 'cancelled_by', 'patient_name', 'doctor_name', 'cancelled_userName')
                        ->WHERE(['id' => $id])->get();
                        
            if($RegComments->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $RegComments);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $RegComments->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleRegComments :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function getAllCommentsByPatientID($id) {
        try {
            $RegComments = ViewRegComments::SELECT('id', 'doctor_id', 'patient_detail_id', 'DateTime', 'narrated_by', 'comments', 'alertType', 'blockAppt', 'patient_type', 'cancel', 'cancelled_at', 'cancelled_by', 'patient_name', 'doctor_name', 'cancelled_userName')
                        ->WHERE(['patient_detail_id' => $id])->get();
                        
            return $this->sendResponse(1,200, 'Success', 'data', $RegComments);
          
        } catch(\Exception $e) {
            Log::debug('API GetSingleRegComments :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function DeleteRegComments(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'regComments_id' => 'required|integer|exists:tbl_RegComments,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $RegComments = RegComments::find($request->regComments_id);
            $RegComments->is_active = 0;
            $RegComments->updated_by = $request->user_id;
            $RegComments->updated_at = date('Y-m-d H:i:s');
            $RegComments->update();

            if($RegComments->id) {
                return $this->sendResponse(1,200, 'Comments deleted successfully', 'regComments_id', $RegComments->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteCancelReason:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function GetCancelledRegComments(Request $request) {
        try {

            $RegComments = ViewRegComments::SELECT('id', 'doctor_id', 'patient_detail_id', 'DateTime', 'narrated_by', 'comments', 'alertType', 'blockAppt', 'patient_type', 'cancel', 'cancelled_at', 'cancelled_by', 'patient_name', 'doctor_name', 'cancelled_userName')
                        ->WHERE(['cancel' => 1])
                        ->ORDERBY('id', 'ASC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $RegComments);
            
        } catch(\Exception $e) {
            Log::debug("API GetRegComments:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
          
    public function AddTypesAbuse(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'name' => 'required|string|min:3|max:200',
                'short_code' => 'nullable|string|min:1|max:50'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $typesAbuse = TypesAbuse::create([
                    'name' => $request->name,
                    'short_code' => $request->short_code,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($typesAbuse->id) {
                    return $this->sendResponse(1,200, 'TypesAbuse created successfully', 'typesAbuse_id', $typesAbuse->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddTypesAbuse :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
            
    public function UpdateTypesAbuse(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'typesAbuse_id' => 'required|integer|exists:type_abuse,id,is_active,1',
                'name' => 'required|string|min:3|max:200',
                'short_code' => 'nullable|string|min:1|max:50'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = TypesAbuse::where(['id' => $request->typesAbuse_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $typesAbuse = TypesAbuse::find($request->typesAbuse_id);
                    $typesAbuse->name = $request->name;
                    $typesAbuse->short_code = $request->short_code;
                    $typesAbuse->updated_by = $request->user_id;
                    $typesAbuse->updated_at = date('Y-m-d H:i:s');
                    $typesAbuse->update();
                    
                    if($request->typesAbuse_id) {
                        $field_names = [
                            'name' => 'TypesAbuse name updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $typesAbuse->id, 'MasterLog', 'TypesAbuse', $old_value, $typesAbuse, $field_names);
                        return $this->sendResponse(1,200, 'TypesAbuse updated successfully', 'typesAbuse_id', $request->typesAbuse_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateTypesAbuse :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function GetTypesAbuse(Request $request) {
        try {

            $typesAbuse = TypesAbuse::SELECT('id', 'name')
                        ->WHERE(['is_active' => 1])
                        ->ORDERBY('id', 'ASC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $typesAbuse);
            
        } catch(\Exception $e) {
            Log::debug("API GetTypesAbuse:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    } 
    
    public function GetSingleTypesAbuse($id) {
        try {
            $typesAbuse = TypesAbuse::SELECT('id',  'name')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($typesAbuse->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $typesAbuse);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $typesAbuse->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleTypesAbuse :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    } 
    
    public function DeleteTypesAbuse(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'typesAbuse_id' => 'required|integer|exists:type_abuse,id,is_active,1',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $typesAbuse = TypesAbuse::find($request->typesAbuse_id);
            $typesAbuse->is_active = 0;
            $typesAbuse->updated_by = $request->user_id;
            $typesAbuse->updated_at = date('Y-m-d H:i:s');
            $typesAbuse->update();

            if($typesAbuse->id) {
                return $this->sendResponse(1,200, 'TypesAbuse deleted successfully', 'typesAbuse_id', $typesAbuse->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteTypesAbuse:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
}
