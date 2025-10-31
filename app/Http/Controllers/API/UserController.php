<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\User;
use App\ViewHospitalDetail;
use App\UserAuditLog;
use App\SecurityToken;
use App\ViewUser;
use App\ViewDoctorList;
use App\ViewDepartmentDoctorcount;
use App\PhoneEnquiry;
use App\DoctorFee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\ThrottlesLogins;  // include the trait
//use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;

use \Validator;
use Illuminate\Support\Facades\Artisan;

class UserController extends BaseController
{

    use ThrottlesLogins; // use the trait
    protected  $maxAttempts = 5;
    protected $decayMinutes = 30; // In minutes
    protected $warning = 3; // Where we should show the warning message (attempts count)
    
        public function clearLaravelCache()
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        return response()->json(['status' => 1, 'message' => 'Config and cache cleared']);
    }

    public function GetCenterList(Request $request) {
        try {
            $client = ViewHospitalDetail::SELECT('id', 'english_name', 'short_name')
                    ->ORDERBY('english_name', 'ASC')->get();
            return $this->sendResponse(1, 200, 'Success', 'data', $client);
        } catch(\Exception $e) {
            Log::debug("API GetBranchList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    // public function signin(Request $request) {
    //     if ($this->hasTooManyLoginAttempts($request)) { // checking form maximum login attempts
    //         return $this->prepareLockMessage($request); // to customize the  message, you can use the below-commented default method
    //         //return $this->sendLockoutResponse($request); // laravel own response
    //     }
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'hospital_detail_id' => 'required|integer|exists:hospital_details,id,is_active,1',
    //             'username' => 'required|string|exists:users,username,is_active,1',
    //             'password' => 'required',
    //         ]);
    //         if ($validator->fails()) {
    //             return $this->sendResponse(0, 200, $validator->errors()->first(), '');
    //         } 

    //         $credentials = request(['username', 'password']);
    //         if (!Auth::attempt($credentials)) {                 // login failed
    //             $this->incrementLoginAttempts($request);        // increment the attempt count
                
    //             if ($this->hasTooManyLoginAttempts($request)) { // checking form maximum login attempts
    //                 return $this->prepareLockMessage($request);
                  
    //             } else {
    //                 $attempts_count = $this->limiter()->attempts($this->throttleKey($request)); // number of attempts performed
    //                 $message = "The username or password you have entered is incorrect.";
    //                 if($attempts_count >= $this->warning) {         // before locking adding a warning message. 'warning' variable will handle this
    //                     $remaining = $this->maxAttempts-$attempts_count;    // number of attempts left
    //                     $message .="Your account will be blocked after ".($remaining)." failed attempts.";
    //                 }
    //                 //return $message;
    //                 return $this->sendResponse(0, 200, $message);
    //             }
    //         }

    //         $this->clearLoginAttempts($request);
        
    //         $user = User::where(['username' => $request->username, 'hospital_detail_id' => $request->hospital_detail_id, 'is_active' => 1, 'user_status'=> 1])->first();
    //         if(!empty($user)) {
    //             if($user->account_status == 1) {
    //                 if (! $user || ! Hash::check($request->password, $user->password)) {
    //                     $user->tokens()->where('name', $request->username)->delete();
    //                     return $this->sendResponse(0, 200, 'Invalid Password', '');
    //                 }
    //                 $role_id = $user->role_id;

    //                 $token_count = $user->tokens()->where('personal_access_tokens.name', $request->username)->count();
                    
    //                 $user->tokens()->where('tokenable_id', $user->id)->delete();

    //                 $roles = ['-','Super Admin','Admin','Front Office','Doctor','Nurse','Accountant','Billing','Cashier','Coder','Auditor','Demo'];
    //                 $token = $user->createToken($request->username)->plainTextToken;
    //                 $response = [];
    //                 $response['token']      = $token;
    //                 $response['user_id']    = $user->id;
    //                 $response['first_name'] = $user->first_name;
    //                 $response['full_name']  = $user->full_name;
    //                 $response['middle_name'] = $user->middle_name;
    //                 $response['last_name']  = $user->last_name;
    //                 $response['mobile']     = $user->current_mobile;
    //                 $response['role_id']    = $user->role_id;
    //                 $response['role_name']  = $roles[$user->role_id];
    //                 $response['hospital_detail_id'] = $user->hospital_detail_id;
    //                 $log_id = $this->UserAuditLogSave($user->id, 'UserLogin', date('Y-m-d H:i:s'), '', $user->username);

    //                 $response['log_id']  = $log_id;
    //                 return $this->sendResponse(1, 200, 'Login Success', 'data', $response);
    //             } else {
    //                 return $this->sendResponse(0, 200, 'You account has been locked. Please Contact Admin', '');
    //             }

    //         } 
    //         return $this->sendResponse(0, 200, 'Username not found. Contact Admin', '');
    //     } catch(\Exception $e) {
    //         Log::debug('API Login:: '.$e->getMessage());
    //         return response()->json([
    //             'status' => 0,
    //             'message'   =>  'Something went wrong try again after sometime.',
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }


    public function signin(Request $request) {
        if ($this->hasTooManyLoginAttempts($request)) {
            return $this->prepareLockMessage($request);
        }
        try {
            $validator = Validator::make($request->all(), [
                'hospital_detail_id' => 'required|integer|exists:hospital_details,id,is_active,1',
                'username' => 'required|string|exists:users,username,is_active,1',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
    
            $credentials = ['username' => $request->username, 'password' => $request->password];
            if (!Auth::attempt($credentials)) {
                $this->incrementLoginAttempts($request);
                
                if ($this->hasTooManyLoginAttempts($request)) {
                    return $this->prepareLockMessage($request);
                } else {
                    $attempts_count = $this->limiter()->attempts($this->throttleKey($request));
                    $message = "The username or password you have entered is incorrect.";
                    if ($attempts_count >= $this->warning) {
                        $remaining = $this->maxAttempts - $attempts_count;
                        $message .= " Your account will be blocked after " . $remaining . " failed attempts.";
                    }
                    return $this->sendResponse(0, 200, $message);
                }
            }
    
            $this->clearLoginAttempts($request);
            
            $user = User::where([
                'username' => $request->username,
                'hospital_detail_id' => $request->hospital_detail_id,
                'is_active' => 1,
                'user_status' => 1
            ])->first();
    
            if (!empty($user)) {
                if ($user->account_status == 1) {
                    $user->tokens()->where('tokenable_id', $user->id)->delete();
    
                    $roles = ['-', 'Super Admin', 'Admin', 'Front Office', 'Doctor', 'Nurse', 'Accountant', 'Billing', 'Cashier', 'Coder', 'Auditor', 'Demo'];
                    $token = $user->createToken($request->username)->plainTextToken;
                    $response = [
                        'token' => $token,
                        'user_id' => $user->id,
                        'first_name' => $user->first_name,
                        'full_name' => $user->full_name,
                        'middle_name' => $user->middle_name,
                        'last_name' => $user->last_name,
                        'mobile' => $user->current_mobile,
                        'role_id' => $user->role_id,
                        'role_name' => $roles[$user->role_id] ?? 'Unknown',
                        'hospital_detail_id' => $user->hospital_detail_id,
                        'log_id' => $this->UserAuditLogSave($user->id, 'UserLogin', date('Y-m-d H:i:s'), '', $user->username)
                    ];
                    return $this->sendResponse(1, 200, 'Login Success', 'data', $response);
                } else {
                    return $this->sendResponse(0, 200, 'Your account has been locked. Please Contact Admin', '');
                }
            } 
            return $this->sendResponse(0, 200, 'Username not found. Contact Admin', '');
        } catch (\Exception $e) {
            Log::debug('API Login:: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ], 200);
        }
    }

    public function login_old(Request $request) {
        if ($this->hasTooManyLoginAttempts($request)) { // checking form maximum login attempts
            return $this->prepareLockMessage($request); // to customize the  message, you can use the below-commented default method
            //return $this->sendLockoutResponse($request); // laravel own response
        }
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 

            $credentials            = request(['username', 'password']);
            if (!Auth::attempt($credentials)) {                 // login failed
                $this->incrementLoginAttempts($request);        // increment the attempt count
                
                if ($this->hasTooManyLoginAttempts($request)) { // checking form maximum login attempts
                    return $this->prepareLockMessage($request);
                  
                } else {
                    $attempts_count = $this->limiter()->attempts($this->throttleKey($request)); // number of attempts performed
                    $message = "The username or password you have entered is incorrect.";
                    if($attempts_count >= $this->warning) {         // before locking adding a warning message. 'warning' variable will handle this
                        $remaining = $this->maxAttempts-$attempts_count;    // number of attempts left
                        $message .="Your account will be blocked after ".($remaining)." failed attempts.";
                    }
                    //return $message;
                    return $this->sendResponse(0, 200, $message);
                }
            }

            $this->clearLoginAttempts($request);
        
            $user = User::where(['username' => $request->username, 'is_active' => 1, 'user_status'=> 1])->first();
            if(!empty($user)) {
                if($user->account_status == 1) {
                    if (! $user || ! Hash::check($request->password, $user->password)) {
                        $user->tokens()->where('name', $request->username)->delete();
                        return $this->sendResponse(0, 200, 'Invalid Password', '');
                    }
                    $role_id = $user->role_id;

                    $token_count = $user->tokens()->where('personal_access_tokens.name', $request->username)->count();
                    
                    $user->tokens()->where('tokenable_id', $user->id)->delete();

                    $roles = ['-','Super Admin','Admin','Front Office','Doctor','Nurse','Accountant','Billing','Cashier','Coder','Auditor','Demo'];
                    $token = $user->createToken($request->username)->plainTextToken;
                    $response = [];
                    $response['token']      = $token;
                    $response['user_id']    = $user->id;
                    $response['first_name'] = $user->first_name;
                    $response['full_name']  = $user->full_name;
                    $response['middle_name'] = $user->middle_name;
                    $response['last_name']  = $user->last_name;
                    $response['mobile']     = $user->current_mobile;
                    $response['role_id']    = $user->role_id;
                    $response['role_name']  = $roles[$user->role_id];
                    $response['hospital_detail_id'] = $user->hospital_detail_id;
                    $log_id = $this->UserAuditLogSave($user->id, 'UserLogin', date('Y-m-d H:i:s'), '', $user->username);

                    $response['log_id']  = $log_id;
                    return $this->sendResponse(1, 200, 'Login Success', 'data', $response);
                } else {
                    return $this->sendResponse(0, 200, 'You account has been locked. Please Contact Admin', '');
                }

            } 
            return $this->sendResponse(0, 200, 'Username not found. Contact Admin', '');
        } catch(\Exception $e) {
            Log::debug('API Login:: '.$e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function username()
    {
        return 'username';
    }

    public function ToMinutes($seconds=0) {
        $minutes = 0;
        if($seconds > 0) {
            return gmdate("i:s", $seconds);
            //$minutes = intval($seconds/60);
        }
        return $minutes;
    }

    public function prepareLockMessage($request)
    {
        try {
            $this->fireLockoutEvent($request);          // checking the locking event
            $seconds = $this->limiter()->availableIn(   // reading how much seconds user should wait
                $this->throttleKey($request)
            );
            if($seconds < 60) {                         // processing the remaining seconds - start
                $minutes = $seconds;                    // a few seconds are remaining
                $timer = "seconds";
            } else {
                $minutes = $this->ToMinutes($seconds);  // converting the seconds into minutes
                $timer = "minutes";
            }
            $message = "Your account has been blocked. Please wait for ".$minutes." ".$timer.".";
            return $this->sendResponse(0, 200, $message);

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input($this->username())).'|'.$request->ip();
    }

    public function clearlogin(Request $request) {
        $this->clearLoginAttempts($request);
        return $this->sendResponse(1, 200, "success");
    }

    public function UserLogout(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'log_id' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $user_id = $request->user_id;
                $log_id  = $request->log_id;

                $user = User::where(['id' => $user_id, 'is_active' => 1])->first();

                $logs = $this->UserAuditLogUpdate($user_id, $log_id);

                $user->tokens()->where('tokenable_id', $user_id)->delete();

                return $this->sendResponse(1, 200, 'Logout Success', '', '');

            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        
        } catch(\Exception $e) {
            Log::debug('API Logout:: '.$e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }

    //Only minimum fields
    public function GetDoctorList(Request $request) {
        try {
            $doctor = ViewDoctorList::SELECT('id', 'full_name', 'department_name', 'department_id', 'hospital_short_name','slot_interval')->get();

            return $this->sendResponse(1, 200, 'Success', 'data', $doctor);

        } catch(\Exception $e) {
            Log::debug('API GetDoctorList:: '.$e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }

    //All details
    public function ViewDoctorList(Request $request) {
        try {
            // Fetch doctor details
            $doctors = ViewDoctorList::SELECT(
                'id', 'username', 'first_name', 'middle_name', 'last_name', 'full_name', 'current_mobile', 'current_email', 'current_address_line', 'current_address_postbox', 'current_city_name', 'current_region_name', 'current_country_name', 'location', 'hospital_short_name', 'role_name', 'department_name', 'gender_name', 'nationality_name', 'title_name', 'job_name', 'emp_doj', 'emp_out', 'profile_img', 'signature1', 'signature2', 'signature3', 'user_status', 'account_status', 'current_city_id', 'current_region_id', 'current_country_id', 'gender_id', 'title_id', 'nationality_id', 'job_title_id', 'role_id', 'department_id', 'qualification_id', 'qualification', 'hospital_detail_id', 'arabic_name', 'slot_interval', 'view_appointment', 'license_no', 'expiry_date', 'department_category_id', 'department_category_name', 'notify_expiry_days', 'clinician_type', 'em_guidelines', 'em_validator', 'lock_encounter_days', 'maternity_chart', 'followUp_required_EMR', 'child_mental_health', 'disable_SMS_doctor', 'disable_exam_normal', 'copy_prescription', 'active', 'unsigned_charts', 'refresh_time_unsigned_charts', 'morningShift_act', 'morningShift_block', 'eveningShift_act', 'eveningShift_block', 'fullShift_act', 'fullShift_block', 'ramadanShift_act', 'ramadanShift_block'
            )->get();
    
            // Fetch consultation fees for all doctors in one query
            $doctorFees = DoctorFee::where('is_active', 1)
                ->select('doctor_id', 'consultation', 'charges', 'created_at', 'updated_at')
                ->get()
                ->groupBy('doctor_id')
                ->map(function ($fees) {
                    return $fees->map(function ($fee) {
                        return [
                            'consultation' => $fee->consultation,
                            'charge' => $fee->charges,
                            'created_at' => $fee->created_at,
                            'updated_at' => $fee->updated_at,
                        ];
                    })->toArray();
                })->toArray();
    
            // Merge consultation fees into doctor details
            $doctors = $doctors->map(function ($doctor) use ($doctorFees) {
                $doctorArray = $doctor->toArray();
                $doctorArray['consultations'] = $doctorFees[$doctor->id] ?? [];
                return $doctorArray;
            })->toArray();
    
            // Prepare response
            $image_path = config('app.image_path');
            $resp = [];
            $resp['image_path'] = $image_path . 'profile/';
            $resp['doctors'] = $doctors;
    
            return $this->sendResponse(1, 200, 'Success', 'data', $resp);
    
        } catch (\Exception $e) {
            Log::debug('API ViewDoctorList:: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Something went wrong, try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function GetDoctorByDepartment(Request $request) {
        try {
            $department_id = $request->department_id;
            $hospital_detail_id = $request->hospital_detail_id;
    
            $query = ViewDoctorList::select('id', 'full_name', 'selected_color','department_name','profile_img')
                ->where('hospital_detail_id', $hospital_detail_id);
    
            if ($department_id != 0) {
                $query->where('department_id', $department_id);
            }
    
            $doctor = $query->get();
            $image_path = config('app.image_path');
            $resp = [];
            $resp['image_path'] = $image_path . 'profile/';
            $resp['doctors'] = $doctor;
    
            return $this->sendResponse(1, 200, 'Success', 'data', $resp);
    
        } catch(\Exception $e) {
            Log::debug('API GetDoctorByDepartment:: '.$e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function GetDepartmentDoctorCount(Request $request) {
        try {
            $doctor = ViewDepartmentDoctorcount::SELECT('id', 'name', 'category_dept', 'user_count')->get();

            return $this->sendResponse(1, 200, 'Success', 'data', $doctor);

        } catch(\Exception $e) {
            Log::debug('API GetDoctorByDepartment:: '.$e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function GetStaffList(Request $request) {
        try {
            $doctor = ViewUser::SELECT('id', 'full_name')
                    ->WHERE(['role_id' => 5])->get();

            return $this->sendResponse(1, 200, 'Success', 'data', $doctor);

        } catch(\Exception $e) {
            Log::debug('API GetStaffList:: '.$e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function GetProfile(Request $request) {
        try {
            //$token = $request->api_token;
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $user_id    = $request->user_id;
                $user = ViewUser::SELECT('id', 'first_name', 'middle_name', 'last_name', 'full_name', 'username', 'current_mobile', 'current_email', 'current_address_line', 'current_address_postbox', 'current_city_name', 'current_region_name', 'current_country_name', 'center_name', 'department_name', 'gender_name', 'nationality_name', 'title_name', 'job_name', 'emp_doj', 'profile_img', 'signature1', 'role_id', 'account_status', 'user_status')
                    ->where(['id' => $user_id])->get();
                $response = [];
                if(count($user) == 1) {
                    if($user[0]->user_status == 1 && $user[0]->account_status == 1) {
                        $image_path = config('app.image_path');
                        $response['user_id']        = $user[0]->id;
                        $response['first_name']     = $user[0]->first_name;
                        $response['middle_name']    = $user[0]->middle_name;
                        $response['last_name']      = $user[0]->last_name;
                        $response['full_name']      = $user[0]->full_name;
                        $response['username']       = $user[0]->username;
                        $response['mobile']         = $user[0]->current_mobile;
                        $response['email']          = $user[0]->current_email;
                        $response['gender']         = $user[0]->gender_name;
                        $response['emp_doj']        = $user[0]->emp_doj;
                        $response['address_line1']      = $user[0]->current_address_line;
                        $response['current_address_postbox']      = $user[0]->current_address_postbox;
                        $response['city_name']          = $user[0]->current_city_name;
                        $response['region_name']        = $user[0]->current_region_name;
                        $response['country_name']       = $user[0]->current_country_name;
                        $response['center_name']        = $user[0]->center_name;
                        $response['department_name']    = $user[0]->department_name;
                        $response['nationality']        = $user[0]->nationality_name;
                        $response['job_name']           = $user[0]->job_name;
                        $response['title_name']         = $user[0]->title_name;
                        $response['digital_signature']  = $user[0]->signature1;
                        $response['account_status']     = $user[0]->account_status;
                        $response['user_status']        = $user[0]->user_status;
                        $response['profile_img']        = $image_path.'profile/'.$user[0]->profile_img;
                        $response['image_path']         = $image_path;
                        return $this->sendResponse(1, 200, 'User Profile Success', 'data', $response);
                    } else {
                        return $this->sendResponse(2, 200, 'Account has been Locked or In-Active. Please Contact Admin', '');
                    }
                } else {
                    return $this->sendResponse(2, 200, 'Unautorized user. Logout Then Login again to continue', '');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        
        } catch(\Exception $e) {
            Log::debug("API GetProfile:: ".$e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }


    public function UpdatePassword(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'password' => 'required|min:8|max:16',
                'old_password' => 'required|min:5|max:16',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 401, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $userId         = $request->user_id;
                $password       = $request->password;
                $old_password   = $request->old_password;
                if(strlen($password) >= 8) {
                    if(strlen($password) <= 16 ){ 
                        $user = User::where(['id' => $request->user_id, 'is_active' => 1, 'user_status'=> 1])->first();
                        if(!empty($user)) {
                            if($user->account_status == 1) {
                                if (! $user || ! Hash::check($old_password, $user->password))
                                {
                                    return $this->sendResponse(0, 200, 'Sorry, your old password was not matching. Please try again', '');
                                }
                                $user = User::find($userId);
                                $user->password = Hash::make($password);
                                $user->update();
                                
                                $update_logs = $this->UpdateSpecificFieldLog($userId, $userId, 'UserLog', 'User',  $old_password, $password, 'password', 'Password changed', 'Password');

                                return $this->sendResponse(1, 200, 'Password Updated Successfully', '');
                            } else {
                                return $this->sendResponse(2, 200, 'Account has been Locked or In-Active. Please Contact Admin', '');
                            }
                        } else {
                            return $this->sendResponse(2, 401, 'Unautorized user. Logout Then Login again to continue', '');
                        }
                    } else {
                        return $this->sendResponse(0, 200, 'Password should be maximum 16 characters', '');
                    }
                } else {
                    return $this->sendResponse(0, 200, 'Password should be minimum 8 characters', '');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug("API UpdatePassword:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function CreateUser(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer',
                'title_id'      => 'required|integer',
                'first_name'    => 'required|string|min:3|max:30',
                'middle_name'   => 'nullable|string|min:0|max:20',
                'last_name'     => 'required|string|min:1|max:30',
                'role_id'       => 'required|integer',
                'job_title_id'  => 'required|integer',
                'gender_id'     => 'required|integer',
                'nationality_id' => 'required|integer',
                'emp_doj'       => 'required', 
                'user_status'   => 'required|integer',
                'username'      => 'required|string|min:3|max:30',
                'password'      => 'required|string|min:3|max:100',
                'account_status' => 'required|integer',
                'profile_img'   => 'mimes:jpeg,jpg,png|required|max:5120',
                'signature1' => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'signature2' => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'signature3' => 'nullable|mimes:jpeg,jpg,png|max:5120',
                //'digital_signature'     => 'mimes:jpeg,jpg,png|required|max:5120',
                'hospital_detail_id'    => 'required|integer',
                'department_id'         => 'nullable|integer',
                'appt_interval'         => 'nullable|integer',
                'selected_color'         => 'nullable|string|min:3|max:30',
                'view_other_doctor_appt'         => 'nullable|integer|in:1,0',
                'view_in_appt'         => 'nullable|integer|in:1,0',
                'short_name'         => 'nullable|string|min:3|max:30',
                'dob'         => 'required|date',
                'marital_status_id'         => 'nullable|integer',
                'religion_id'         => 'nullable|integer',
                'father_or_Husband'         => 'nullable|string|min:3|max:200',
                'doc_qualification'         => 'nullable|string|min:3|max:200',
                'doc_profile_id'         => 'nullable|integer',
                'current_address_line' => 'nullable|string|min:0|max:200',
                'current_address_postbox' => 'nullable|string|min:0|max:200',
                'current_country_id'    => 'nullable|integer',
                'current_city_id'       => 'nullable|integer',
                'current_region_id'     => 'nullable|integer',
                'current_email'         => 'nullable|email|max:50',
                'current_mobile'        => 'nullable|string|min:7|max:20',
                'current_phone'        => 'nullable|string|min:7|max:20',
                'perm_address_line' => 'nullable|string|min:0|max:200',
                'perm_address_postbox' => 'nullable|string|min:0|max:200',
                'perm_country_id'    => 'nullable|integer',
                'perm_city_id'       => 'nullable|integer',
                'perm_region_id'     => 'nullable|integer',
                'perm_email'         => 'nullable|email|max:50',
                'perm_mobile'        => 'nullable|string|min:7|max:20',
                'perm_phone'        => 'nullable|string|min:7|max:20',
                'emp_code'        => 'nullable|string|min:3|max:100',
                'contract_no'        => 'nullable|string|min:3|max:100',
                'offer_no'        => 'nullable|string|min:3|max:100',
                'location_id'   => 'nullable|integer',
                'visa_type_id' => 'nullable|integer',
                'ledger_no'        => 'nullable|string|min:3|max:100',
                'grade'        => 'nullable|string|min:3|max:100',
                'sponsor'        => 'nullable|string|min:3|max:100',
                'accessCard_no'        => 'nullable|string|min:3|max:100',
                'scheduler_access'        => 'nullable|integer|in:1,0',
                'insurance_card'        => 'nullable|integer|in:1,0',
                'accomodation'        => 'nullable|integer|in:1,0',
                'mother_name'        => 'nullable|string|min:3|max:100',
                'qualification_id'        => 'nullable|integer',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }  
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
            if($this->VerifyPageAccess('user', 'is_add') === false){
                return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            }
            if (strrpos($request->username, ' ') === false){
                $user_count = User::where(['username' => $request->username, 'is_active' => 1])->get();
                if($user_count->count() == 0) {
                    $mobile_count = User::where(['current_mobile' => $request->current_mobile, 'is_active' => 1])->get();
                    if($mobile_count->count() == 0) {
                        $email_count = User::where(['current_email' => $request->current_email, 'is_active' => 1])->get();
                        if($email_count->count() == 0) {
                            $profile_img = 'default.png';
                            $signature1 = '';
                            $signature2 = '';
                            $signature3 = '';
                            if($request->hasFile('profile_img'))
                            {
                                $profile_logo = $request->file('profile_img');
                                $extension = $request->file('profile_img')->extension();
                                $profile_img = time().'1_'.str_replace(' ', '_',$request->username).'.'.$extension;                    
                                $destinationPath = public_path('/profile');
                                $profile_logo->move($destinationPath, $profile_img);
                            }

                            if ($request->hasFile('signature1')) {
                                $signature1_file = $request->file('signature1');
                                $extension = $signature1_file->extension();
                                $signature1 = time() . '_sig1_' . str_replace(' ', '_', $request->username) . '.' . $extension;
                                $destinationPath = public_path('/profile');
                                $signature1_file->move($destinationPath, $signature1);
                            }
                        
                            if ($request->hasFile('signature2')) {
                                $signature2_file = $request->file('signature2');
                                $extension = $signature2_file->extension();
                                $signature2 = time() . '_sig2_' . str_replace(' ', '_', $request->username) . '.' . $extension;
                                $destinationPath = public_path('/profile');
                                $signature2_file->move($destinationPath, $signature2);
                            }
                        
                            if ($request->hasFile('signature3')) {
                                $signature3_file = $request->file('signature3');
                                $extension = $signature3_file->extension();
                                $signature3 = time() . '_sig3_' . str_replace(' ', '_', $request->username) . '.' . $extension;
                                $destinationPath = public_path('/profile');
                                $signature3_file->move($destinationPath, $signature3);
                            }

                            // $password = rand(11111111,99999999);
                            $adduser = User::create([
                                'title_id' => $request->title_id,
                                'first_name' => $request->first_name,
                                'middle_name' => $request->middle_name,
                                'last_name' => $request->last_name,
                                'full_name' => $request->first_name.' '.$request->middle_name.' '.$request->last_name,
                                'username' => $request->username,
                                'password' => Hash::make($request->password),
                                'hospital_detail_id' => $request->hospital_detail_id,
                                'department_id' => $request->department_id,
                                'profile_img' => $profile_img,
                                'signature1' => $signature1,
                                'signature2' => $signature2,
                                'signature3' => $signature3,
                                'role_id' => $request->role_id,
                                'job_title_id' => $request->job_title_id,
                                'gender_id' => $request->gender_id,
                                'nationality_id' => $request->nationality_id,
                                'emp_doj' => date("Y-m-d", strtotime($request->emp_doj)),
                                'user_status' => $request->user_status,
                                'account_status' => $request->account_status,
                                'appt_interval' => $request->appt_interval,
                                'selected_color' => $request->selected_color,
                                'view_other_doctor_appt' => $request->view_other_doctor_appt,
                                'view_in_appt'  => $request->view_in_appt,
                                'short_name' => $request->short_name,
                                'dob' => $request->dob,
                                'marital_status_id' => $request->marital_status_id,
                                'religion_id'  => $request->religion_id,
                                'father_or_Husband' => $request->father_or_Husband,
                                'doc_qualification' => $request->doc_qualification,
                                'doc_profile_id' => $request->doc_profile_id,
                                'current_address_line' => $request->current_address_line,
                                'current_address_postbox' => $request->current_address_postbox,
                                'current_country_id' => $request->current_country_id,
                                'current_city_id'  => $request->current_city_id,
                                'current_region_id' => $request->current_region_id,
                                'current_email' => $request->current_email,
                                'current_mobile'  => $request->current_mobile,
                                'current_phone'  => $request->current_phone,
                                'perm_address_line' => $request->perm_address_line,
                                'perm_address_postbox' => $request->perm_address_postbox,
                                'perm_country_id'  => $request->perm_country_id,
                                'perm_city_id'  => $request->perm_city_id,
                                'perm_region_id' => $request->perm_region_id,
                                'perm_email' => $request->perm_email,
                                'perm_mobile' => $request->perm_mobile,
                                'perm_phone' => $request->perm_phone,
                                'emp_code'  => $request->emp_code,
                                'contract_no' => $request->contract_no,
                                'offer_no' => $request->offer_no,
                                'location_id'  => $request->location_id,
                                'visa_type_id' => $request->visa_type_id,
                                'ledger_no'  => $request->ledger_no,
                                'grade'  => $request->grade,
                                'sponsor'  => $request->sponsor,
                                'accessCard_no'  => $request->accessCard_no,
                                'scheduler_access'  => $request->scheduler_access,
                                'insurance_card'  => $request->insurance_card,
                                'accomodation'  => $request->accomodation,
                                'mother_name'  => $request->mother_name,
                                'qualification_id'  => $request->qualification_id,
                                'created_by' => $request->user_id,
                                'updated_by' => $request->user_id,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);

                            if($adduser->id) {
                                $resp = [];
                                $resp['user_id'] = $adduser->id;
                                return $this->sendResponse(1,200, 'User created successfully', 'data', $resp);
                            } else {
                                return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', '');
                            }
                        } else {
                            return $this->sendResponse(0,200, 'Email ID already exist', '');
                        }
                    } else {
                        return $this->sendResponse(0,200, 'Mobile Number already exist', '');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Username already exist', '');
                }
            } else {
                return $this->sendResponse(0,200, 'Space not allowed in Username', '');
            }

        
        } catch(\Exception $e) {
            Log::debug("API CreateUser:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function UpdateUser(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id'            => 'required|integer|exists:users,id,is_active,1',
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'title_id'      => 'required|integer',
                'first_name'    => 'required|string|min:3|max:30',
                'middle_name'   => 'nullable|string|min:0|max:20',
                'last_name'     => 'required|string|min:1|max:30',
                'role_id'       => 'required|integer',
                'job_title_id'  => 'required|integer',
                'gender_id'     => 'required|integer',
                'nationality_id' => 'required|integer',
                'emp_doj'       => 'required', 
                'user_status'   => 'required|integer',
                'username'      => 'required|string|min:3|max:30',
                'password'      => 'nullable|string|min:3|max:100',
                'account_status' => 'required|integer',
                'profile_img'   => 'mimes:jpeg,jpg,png|max:5120',
                'signature1' => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'signature2' => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'signature3' => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'hospital_detail_id'    => 'required|integer',
                'department_id'         => 'nullable|integer',
                'appt_interval'         => 'nullable|integer',
                'selected_color'         => 'nullable|string|min:3|max:30',
                'view_other_doctor_appt' => 'nullable|integer|in:1,0',
                'view_in_appt'         => 'nullable|integer|in:1,0',
                'short_name'         => 'nullable|string|min:3|max:30',
                'dob'         => 'required|date',
                'marital_status_id'         => 'nullable|integer',
                'religion_id'         => 'nullable|integer',
                'father_or_Husband'         => 'nullable|string|min:3|max:200',
                'doc_qualification'         => 'nullable|string|min:3|max:200',
                'doc_profile_id'         => 'nullable|integer',
                'current_address_line' => 'nullable|string|min:0|max:200',
                'current_address_postbox' => 'nullable|string|min:0|max:200',
                'current_country_id'    => 'nullable|integer',
                'current_city_id'       => 'nullable|integer',
                'current_region_id'     => 'nullable|integer',
                'current_email'         => 'nullable|email|max:50',
                'current_mobile'        => 'nullable|string|min:7|max:20',
                'current_phone'        => 'nullable|string|min:7|max:20',
                'perm_address_line' => 'nullable|string|min:0|max:200',
                'perm_address_postbox' => 'nullable|string|min:0|max:200',
                'perm_country_id'    => 'nullable|integer',
                'perm_city_id'       => 'nullable|integer',
                'perm_region_id'     => 'nullable|integer',
                'perm_email'         => 'nullable|email|max:50',
                'perm_mobile'        => 'nullable|string|min:7|max:20',
                'perm_phone'        => 'nullable|string|min:7|max:20',
                'emp_code'        => 'nullable|string|min:3|max:100',
                'contract_no'        => 'nullable|string|min:3|max:100',
                'offer_no'        => 'nullable|string|min:3|max:100',
                'location_id'        => 'nullable|integer',
                'visa_type_id' => 'nullable|integer',
                'ledger_no'        => 'nullable|string|min:3|max:100',
                'grade'        => 'nullable|string|min:3|max:100',
                'sponsor'        => 'nullable|string|min:3|max:100',
                'accessCard_no'        => 'nullable|string|min:3|max:100',
                'scheduler_access'        => 'nullable|integer|in:1,0',
                'insurance_card'        => 'nullable|integer|in:1,0',
                'accomodation'        => 'nullable|integer|in:1,0',
                'mother_name'        => 'nullable|string|min:3|max:100',
                'qualification_id'        => 'nullable|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }  
            if($this->VerifyAuthUser($request->user_id, false) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
            if($this->VerifyPageAccess('user', 'is_edit') === false){
                return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            }
    
            if (strrpos($request->username, ' ') === false){
                $user_count = User::where(['username' => $request->username, 'is_active' => 1])
                            ->where('id', '<>', $request->id)->get();
                if($user_count->count() == 0) {
                    $mobile_count = User::where(['current_mobile' => $request->current_mobile, 'is_active' => 1])
                                ->where('id', '<>', $request->id)->get();
                    if($mobile_count->count() == 0) {
                        $email_count = User::where(['current_email' => $request->current_email, 'is_active' => 1])
                                ->where('id', '<>', $request->id)->get();
                        if($email_count->count() == 0) {
                            $adduser = User::find($request->id);
                            $old_profile_img = $adduser->profile_img;
                            $old_signature1 = $adduser->signature1;
                            $old_signature2 = $adduser->signature2;
                            $old_signature3 = $adduser->signature3;
    
                            if($request->hasFile('profile_img'))
                            {
                                $profile_logo = $request->file('profile_img');
                                $extension = $request->file('profile_img')->extension();
                                $adduser->profile_img = time().'1_'.str_replace(' ', '_',$request->username).'.'.$extension;                    
                                $destinationPath = public_path('/profile');
                                $profile_logo->move($destinationPath, $adduser->profile_img);
                            }
    
                            if ($request->hasFile('signature1')) {
                                $signature1_file = $request->file('signature1');
                                $extension = $signature1_file->extension();
                                $adduser->signature1 = time() . '_sig1_' . str_replace(' ', '_', $request->username) . '.' . $extension;
                                $destinationPath = public_path('/profile');
                                $signature1_file->move($destinationPath, $adduser->signature1);
                            }
                        
                            if ($request->hasFile('signature2')) {
                                $signature2_file = $request->file('signature2');
                                $extension = $signature2_file->extension();
                                $adduser->signature2 = time() . '_sig2_' . str_replace(' ', '_', $request->username) . '.' . $extension;
                                $destinationPath = public_path('/profile');
                                $signature2_file->move($destinationPath, $adduser->signature2);
                            }
                        
                            if ($request->hasFile('signature3')) {
                                $signature3_file = $request->file('signature3');
                                $extension = $signature3_file->extension();
                                $adduser->signature3 = time() . '_sig3_' . str_replace(' ', '_', $request->username) . '.' . $extension;
                                $destinationPath = public_path('/profile');
                                $signature3_file->move($destinationPath, $adduser->signature3);
                            }
    
                            $old_value = User::where(['id' => $request->id, 'is_active' => 1])->get();
    
                            $adduser->title_id = $request->title_id;
                            $adduser->first_name = $request->first_name;
                            $adduser->middle_name = $request->middle_name;
                            $adduser->last_name = $request->last_name;
                            $adduser->full_name = $request->first_name.' '.$request->middle_name.' '.$request->last_name;
                            $adduser->username = $request->username;
                            if ($request->filled('password')) {
                                $adduser->password = Hash::make($request->password);
                            }
                            $adduser->hospital_detail_id = $request->hospital_detail_id;
                            $adduser->role_id = $request->role_id;
                            $adduser->job_title_id = $request->job_title_id;
                            $adduser->gender_id = $request->gender_id;
                            $adduser->nationality_id = $request->nationality_id;
                            $adduser->emp_doj = date("Y-m-d", strtotime($request->emp_doj));
                            $adduser->user_status = $request->user_status;
                            $adduser->account_status = $request->account_status;
                            $adduser->department_id = $request->department_id;
                            $adduser->appt_interval = $request->appt_interval;
                            $adduser->selected_color = $request->selected_color;
                            $adduser->view_other_doctor_appt = $request->view_other_doctor_appt;
                            $adduser->view_in_appt = $request->view_in_appt;
                            $adduser->short_name = $request->short_name;
                            $adduser->dob = $request->dob;
                            $adduser->marital_status_id = $request->marital_status_id;
                            $adduser->religion_id = $request->religion_id;
                            $adduser->father_or_Husband = $request->father_or_Husband;
                            $adduser->doc_qualification = $request->doc_qualification;
                            $adduser->doc_profile_id = $request->doc_profile_id;
                            $adduser->current_address_line = $request->current_address_line;
                            $adduser->current_address_postbox = $request->current_address_postbox;
                            $adduser->current_country_id = $request->current_country_id;
                            $adduser->current_city_id = $request->current_city_id;
                            $adduser->current_region_id = $request->current_region_id;
                            $adduser->current_email = $request->current_email;
                            $adduser->current_mobile = $request->current_mobile;
                            $adduser->current_phone  = $request->current_phone;
                            $adduser->perm_address_line = $request->perm_address_line;
                            $adduser->perm_address_postbox = $request->perm_address_postbox;
                            $adduser->perm_country_id  = $request->perm_country_id;
                            $adduser->perm_city_id  = $request->perm_city_id;
                            $adduser->perm_region_id = $request->perm_region_id;
                            $adduser->perm_email = $request->perm_email;
                            $adduser->perm_mobile = $request->perm_mobile;
                            $adduser->perm_phone = $request->perm_phone;
                            $adduser->emp_code = $request->emp_code;
                            $adduser->contract_no = $request->contract_no;
                            $adduser->offer_no = $request->offer_no;
                            $adduser->location_id = $request->location_id;
                            $adduser->visa_type_id = $request->visa_type_id;
                            $adduser->ledger_no = $request->ledger_no;
                            $adduser->grade = $request->grade;
                            $adduser->sponsor = $request->sponsor;
                            $adduser->accessCard_no = $request->accessCard_no;
                            $adduser->scheduler_access = $request->scheduler_access;
                            $adduser->insurance_card = $request->insurance_card;
                            $adduser->accomodation = $request->accomodation;
                            $adduser->mother_name  = $request->mother_name;
                            $adduser->qualification_id  = $request->qualification_id;
                            $adduser->updated_by = $request->user_id;
                            $adduser->updated_at = date('Y-m-d H:i:s');
                            $adduser->update();
                            
                            $resp = [];
                            $resp['user_id'] = $adduser->id;
    
                            $field_names = [
                                'first_name' => 'Firstname changed', 
                                'middle_name' => 'Middlename changed', 
                                'last_name' => 'Lastname changed',
                                'username' => 'Username changed',
                                'password' => 'Password changed',
                                'profile_img' => 'Profile image updated', 
                                'signature1' => 'Signature1 image updated', 
                                'signature2' => 'Signature2 image updated', 
                                'signature3' => 'Signature3 image updated', 
                                'title_id' => 'Title changed',
                                'role_id' => 'Role changed', 
                                'job_title_id' => 'Job changed',
                                'gender_id' => 'Gender changed', 
                                'nationality_id' => 'Nationality changed',
                                'emp_doj' => 'Date of joining updated',
                                'user_status' => 'User status updated',
                                'account_status' => 'Account status updated',
                                'hospital_detail_id' => 'Hospital detail changed',
                                'department_id' => 'Department changed',
                                'appt_interval' => 'Appointment interval updated',
                                'selected_color' => 'Selected color updated',
                                'view_other_doctor_appt' => 'View other doctor appointment updated',
                                'view_in_appt' => 'View in appointment updated',
                                'short_name' => 'Short name updated',
                                'dob' => 'Date of birth updated',
                                'marital_status_id' => 'Marital status updated',
                                'religion_id' => 'Religion updated',
                                'father_or_Husband' => 'Father or husband updated',
                                'doc_qualification' => 'Doctor qualification updated',
                                'doc_profile_id' => 'Doctor profile updated',
                                'current_address_line' => 'Current address line updated',
                                'current_address_postbox' => 'Current address postbox updated',
                                'current_country_id' => 'Current country updated',
                                'current_city_id' => 'Current city updated',
                                'current_region_id' => 'Current region updated',
                                'current_email' => 'Current email updated',
                                'current_mobile' => 'Current mobile updated',
                                'perm_address_line' => 'Permanent address line updated',
                                'perm_address_postbox' => 'Permanent address postbox updated',
                                'perm_country_id' => 'Permanent country updated',
                                'perm_city_id' => 'Permanent city updated',
                                'perm_region_id' => 'Permanent region updated',
                                'perm_email' => 'Permanent email updated',
                                'perm_mobile' => 'Permanent mobile updated',
                                'emp_code' => 'Employee code updated',
                                'contract_no' => 'Contract number updated',
                                'offer_no' => 'Offer number updated',
                                'location_id' => 'Location updated',
                                'visa_type_id' => 'Visa type updated',
                                'ledger_no' => 'Ledger number updated',
                                'grade' => 'Grade updated',
                                'sponsor' => 'Sponsor updated',
                                'accessCard_no' => 'Access card number updated',
                                'scheduler_access' => 'Scheduler access updated',
                                'insurance_card' => 'Insurance card updated',
                                'accomodation' => 'Accommodation updated',
                                'mother_name' => 'Mother name updated',
                                'qualification_id' => 'Qualification updated'
                            ];
                            $update_logs = $this->UpdateLogs($request->user_id, $adduser->id, 'UserLog', 'User', $old_value, $adduser, $field_names);
    
                            return $this->sendResponse(1,200, 'User updated successfully', 'data', $resp);
                            
                        } else {
                            return $this->sendResponse(0,200, 'Email ID already exist', '');
                        }
                    } else {
                        return $this->sendResponse(0,200, 'Mobile Number already exist', '');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Username already exist', '');
                }
            } else {
                return $this->sendResponse(0,200, 'Space not allowed in Username', '');
            }
        } catch(\Exception $e) {
            Log::debug("API UpdateUser:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetUserListData(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'role_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if ($this->VerifyAuthUser($request->user_id, 0) === false) {
                return $this->sendResponse(0, 200, 'Login user token is not matching. Invalid user or token', '');
            }
            if ($this->VerifyPageAccess('user', 'is_view') === false) {
                return $this->sendResponse(0, 200, "You don't have access for this action. Contact admin");
            }
    
            $image_path = config('app.image_path');
    
            $users = ViewUser::SELECT(
                'id',
                'username',
                'first_name',
                'middle_name',
                'last_name',
                'full_name',
                'current_city_name',
                'current_region_name',
                'current_country_name',
                'center_name',
                'perm_city_name',
                'perm_region_name',
                'perm_country_name',
                'current_address_line',
                'current_address_postbox',
                'perm_address_line',
                'perm_address_postbox',
                'selected_color',
                'current_mobile',
                'current_email',
                'hospital_short_name',
                'role_name',
                'department_id',
                'department_name',
                'gender_name',
                'nationality_name',
                'title_name',
                'job_name',
                'emp_doj',
                'emp_out',
                'profile_img',
                'signature1',
                'signature2',
                'signature3',
                'user_status',
                'account_status',
                'appt_interval',
                'view_other_doctor_appt',
                'current_city_id',
                'current_region_id',
                'current_country_id',
                'perm_city_id',
                'perm_region_id',
                'perm_country_id',
                'dob',
                'gender_id',
                'title_id',
                'view_in_appt',
                'short_name',
                'marital_status_id',
                'marital_status',
                'religion_id',
                'religion',
                'nationality_id',
                'job_title_id',
                'role_id',
                'father_or_Husband',
                'doc_qualification',
                'doc_profile_id',
                'doc_profile',
                'hospital_detail_id',
                'emp_code',
                'contract_no',
                'offer_no',
                'location_id',
                'location',
                'sponsor',
                'accessCard_no',
                'ledger_no',
                'grade',
                'visa_type_id',
                'visa_type',
                'scheduler_access',
                'insurance_card',
                'accomodation',
                'perm_mobile',
                'perm_phone',
                'mother_name',
                'qualification_id',
                'qualification',
                'password'
            )
            ->WHERE('role_id', '!=', 1)
            ->ORDERBY('id', 'DESC')
            ->get();
    
            // Cast numeric fields to integers
            $numericFields = [
                'id',
                'department_id',
                'current_city_id',
                'current_region_id',
                'current_country_id',
                'perm_city_id',
                'perm_region_id',
                'perm_country_id',
                'gender_id',
                'title_id',
                'marital_status_id',
                'religion_id',
                'nationality_id',
                'job_title_id',
                'role_id',
                'doc_profile_id',
                'hospital_detail_id',
                'visa_type_id',
                'appt_interval',
                'user_status',
                'account_status',
                'view_other_doctor_appt',
                'view_in_appt',
                'scheduler_access',
                'insurance_card',
                'accomodation',
                'qualification_id'
            ];
    
            $users = $users->map(function ($user) use ($numericFields) {
                $userArray = $user->toArray();
                foreach ($numericFields as $field) {
                    if (isset($userArray[$field])) {
                        $userArray[$field] = (int)$userArray[$field];
                    }
                }
                return $userArray;
            })->toArray();
    
            $resp = [];
            $resp['image_path'] = $image_path . 'profile/';
            $resp['users'] = $users;
            return $this->sendResponse(1, 200, 'Success', 'data', $resp);
    
        } catch (\Exception $e) {
            Log::debug("API GetUserListData:: " . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function GetSingleUser(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
    
            if ($this->VerifyPageAccess('user', 'is_edit') === false) {
                return $this->sendResponse(0, 200, "You don't have access for this action. Contact admin");
            }
    
            $image_path = config('app.image_path');
    
            $users = ViewUser::SELECT(
                'id',
                'username',
                'first_name',
                'middle_name',
                'last_name',
                'full_name',
                'current_city_name',
                'current_region_name',
                'current_country_name',
                'center_name',
                'perm_city_name',
                'perm_region_name',
                'perm_country_name',
                'current_address_line',
                'current_address_postbox',
                'perm_address_line',
                'perm_address_postbox',
                'selected_color',
                'current_mobile',
                'current_email',
                'hospital_short_name',
                'role_name',
                'department_id',
                'department_name',
                'gender_name',
                'nationality_name',
                'title_name',
                'job_name',
                'emp_doj',
                'emp_out',
                'profile_img',
                'signature1',
                'signature2',
                'signature3',
                'user_status',
                'account_status',
                'appt_interval',
                'view_other_doctor_appt',
                'current_city_id',
                'current_region_id',
                'current_country_id',
                'perm_city_id',
                'perm_region_id',
                'perm_country_id',
                'dob',
                'gender_id',
                'title_id',
                'view_in_appt',
                'short_name',
                'marital_status_id',
                'marital_status',
                'religion_id',
                'religion',
                'nationality_id',
                'job_title_id',
                'role_id',
                'father_or_Husband',
                'doc_qualification',
                'doc_profile_id',
                'doc_profile',
                'hospital_detail_id',
                'emp_code',
                'contract_no',
                'offer_no',
                'location_id',
                'location',
                'sponsor',
                'accessCard_no',
                'ledger_no',
                'grade',
                'visa_type_id',
                'visa_type',
                'scheduler_access',
                'insurance_card',
                'accomodation',
                'perm_mobile',
                'perm_phone',
                'mother_name',
                'qualification_id',
                'qualification',
                'password'
            )
            ->where(['id' => (int)$request->user_id])
            ->get();
    
            if ($users->count() == 1) {
                $user = $users->first()->toArray();
                
                // Cast numeric fields to integers
                $numericFields = [
                    'id',
                    'department_id',
                    'current_city_id',
                    'current_region_id',
                    'current_country_id',
                    'perm_city_id',
                    'perm_region_id',
                    'perm_country_id',
                    'gender_id',
                    'title_id',
                    'marital_status_id',
                    'religion_id',
                    'nationality_id',
                    'job_title_id',
                    'role_id',
                    'doc_profile_id',
                    'hospital_detail_id',
                    'visa_type_id',
                    'appt_interval',
                    'user_status',
                    'account_status',
                    'view_other_doctor_appt',
                    'view_in_appt',
                    'scheduler_access',
                    'insurance_card',
                    'accomodation',
                    'qualification_id'
                ];
    
                foreach ($numericFields as $field) {
                    if (isset($user[$field])) {
                        $user[$field] = (int)$user[$field];
                    }
                }
    
                $resp = [];
                $resp['image_path'] = $image_path . 'profile/';
                $resp['users'] = [$user];
                
                return $this->sendResponse(1, 200, 'Success', 'data', $resp);
            } else {
                return $this->sendResponse(0, 200, 'Record not found', '');
            }
    
        } catch (\Exception $e) {
            Log::debug("API GetSingleUser:: " . $e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function SendOtpResetPassword(Request $request) {
        try {

            if(ViewUser::where(['mobile'=>$request->mobile])->exists()){
                //$sms_controller   = new SmsGatewayController;
                $delete   = $this->DeleteUserTokens($request->mobile);

                $otp = '1111'; //rand(1000, 9999);
                $token = Str::random(64);
                $create = SecurityToken::create([
                    'mobile'      => $request->mobile,
                    'token'  =>  $token,
                    'otp_code'  => $otp,
                    'created_at'     => date('Y-m-d H:i:s')
                ]);

                if($create->id) {
                    $msg = $otp." Valid only 5 mins Regards Unicare";
                    //$sendsms = $sms_controller->SendSMSToUser($request->mobile, $msg, 2);
                    return $this->sendResponse(1, 200, 'Sent OTP to your mobile', 'token_key', $token);
                }
            } else {
                return $this->sendResponse(0, 200, 'Enter Registered mobile number.', '');
            }
        } catch(\Exception $e) {
            Log::debug($e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function ResetUserPassword(Request $request) {
        try {
            $request_no     = $request->mobile_number;
            $request_token  = $request->token_key;
            $smscode        = $request->smscode;
            $password       = $request->password;
            $confirm_password        = $request->confirm_password;

            if(strlen($password) >= 8 && strlen($confirm_password) >= 8) {
                if(strlen($password) <= 16 ){ 
                    if($password == $confirm_password) {
                        $security = SecurityToken::SELECT('created_at', 'otp_code', 'mobile', 'token')
                                    ->WHERE(['token' => $request_token])->get();
                        if($security->count() == 1) {

                            $mobile = $security[0]->mobile;
                            $otp_code = $security[0]->otp_code;
                            $datime = $security[0]->created_at;

                            $start  = date('Y-m-d H:i:s');
                            $date1 = strtotime($datime);
                            $date2 = strtotime($start);
                            $diff = abs($date2 - $date1);
                            

                            $years = floor($diff / (365*60*60*24));
                            

                            $months = floor(($diff - $years * 365*60*60*24)
                                                            / (30*60*60*24));

                            $days = floor(($diff - $years * 365*60*60*24 -
                                        $months*30*60*60*24)/ (60*60*24));
                            
                            $hours = floor(($diff - $years * 365*60*60*24
                                    - $months*30*60*60*24 - $days*60*60*24)
                                                                / (60*60));
                            
                            $minutes = floor(($diff - $years * 365*60*60*24
                                    - $months*30*60*60*24 - $days*60*60*24
                                                        - $hours*60*60)/ 60);
                            
                            $total_mins = $hours + $minutes;
                            if($total_mins < 5) {
                                if($mobile == $request_no) {
                                    if($smscode == $otp_code) {
                                        $user = ViewUser::SELECT('id', 'mobile')
                                                ->WHERE(['mobile' => $mobile])->get();
                                        if($user->count() == 1) {
                                            $userId = $user[0]->id;
                                            $user = User::find($userId);
                                            $user->password = Hash::make($password);;
                                            $user->save();
                                            //$sms_controller   = new SmsGatewayController;
                                            $delete   = $this->DeleteUserTokens($mobile);
                                            return $this->sendResponse(1, 200, 'Your password has been re-setted successfully. Continue to Login', '');
                                        } else {
                                            return $this->sendResponse(0, 200, 'Something went wrong. Try Again after sometime', '');
                                        }

                                    } else {
                                        return $this->sendResponse(0, 200, 'Enter valid OTP code', '');
                                    }
                                } else {
                                    return $this->sendResponse(0, 200, 'Something went wrong. Try Again after sometime', '');
                                }
                            } else {
                                return $this->sendResponse(0, 200, 'The sms code has expired. Please re-send the verification code to try again'.$total_mins, '');
                            }
                        } else {
                            return $this->sendResponse(0, 200, 'Invalid Token Key', '');
                        }
                    } else {
                        return $this->sendResponse(0, 200, 'Confirm password does not match.', '');
                    }
                } else {
                    return $this->sendResponse(0, 200, 'Password should be maximum 16 characters.', '');
                }
            } else {
                return $this->sendResponse(0, 200, 'Password should be minimum 8 characters.', '');
            }
        } catch(\Exception $e) {
            Log::debug($e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }
       
    public function DeleteUser(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'id' => 'required|integer|exists:users,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $user = User::find($request->id);
            $user->is_active = 0;
            $user->updated_by = $request->user_id;
            $user->updated_at = date('Y-m-d H:i:s');
            $user->update();

            if($user->id) {
                return $this->sendResponse(1,200, 'User deleted successfully', 'id', $user->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteUser:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    
}
