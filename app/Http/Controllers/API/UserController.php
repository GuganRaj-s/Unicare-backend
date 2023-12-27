<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\User;
use App\UserAuditLog;
use App\SecurityToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use \Validator;

class UserController extends BaseController
{

    public function login(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 404, 'Some parameter are missing or incorrect parameter.', '');
            } 

            // $category = UserAuditLog::create([
            //     'log_type'=>'UserLogin',
            //     'ip_address'=>'10.10',
            //     'before_update'=>'dfgd',
            //     'after_update'=>'35235dgsd',
            //     'updated_by'=>'reghu',
            //     'user_id'=>1,
            //     'updated_at'=>date('Y-m-d h:i:s')
            // ]);
        
            $user = User::where(['username' => $request->username, 'is_active' => 1])->first();
            if(!empty($user)) {
                if (! $user || ! Hash::check($request->password, $user->password)) {
                    $user->tokens()->where('name', $request->device_id)->delete();
                    return $this->sendResponse(0, 404, 'Invalid Password', '');
                }
                $role_id = $user->role_id;

                $otp_log = false;
                $otp_token = '';

                $token_count = $user->tokens()->where('personal_access_tokens.name', $request->device_id)->count();
                
                $user->tokens()->where('tokenable_id', $user->id)->delete();
                
                //$user->tokens()->where('name', $request->device_id)->delete();
                $roles = ['-', 'Admin', 'Distributor', 'Distributor', 'Retailer', 'API User', 'Office Staff', 'System'];
                $token = $user->createToken($request->device_id)->plainTextToken;
                $response = [];
                $response['token']      = $token;
                $response['user_id']    = $user->id;
                $response['name']       = $user->name;
                $response['identifier'] = $user->user_identifier;
                $response['mobile']     = $user->mobile;
                $response['role_id']    = $user->role_id;
                $response['role_name']  = $roles[$user->role_id];
                $response['otp_log']    = $otp_log; //True means need to validate OTP
                $response['otp_token']  = $otp_token;
                return $this->sendResponse(1, 200, 'Login Success', 'data', $response);
                    

            } 
            return $this->sendResponse(0, 404, 'Username not found. Contact Admin', '');
        } catch(\Exception $e) {
            Log::debug('API Login:: '.$e->getMessage());
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
                'mobile_no' => 'required',
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 401, 'Some parameter are missing or incorrect parameter.', '');
            }
            $mobile_no  = $request->mobile_no;
            $user_id    = $request->user_id;
            $user = ViewUser::select('id', 'user_identifier', 'name', 'role_name', 'parent_name', 'login_id','mobile', 'profile_image', 'user_status', 'recharge_balance', 'payment_balance')
                    ->where(['id' => $user_id, 'mobile'=>$mobile_no])->get();
            $response = [];
            if(count($user) == 1) {
                if($user[0]->user_status == 1) {
                    $response['user_id']        = $user[0]->id;
                    $response['name']           = $user[0]->name;
                    $response['identifier']     = $user[0]->user_identifier;
                    $response['mobile']         = $user[0]->mobile;
                    $response['role_name']      = $user[0]->role_name;
                    $response['parent_name']    = $user[0]->parent_name;
                    $response['login_id']       = $user[0]->login_id;
                    $response['profile_image']  = $user[0]->profile_image;
                    $response['user_balance']   = $user[0]->recharge_balance;
                    $response['payment_balance']    = $user[0]->payment_balance;
                    return $this->sendResponse(1, 200, 'User Profile Success', 'data', $response);
                } else {
                    return $this->sendResponse(2, 401, 'Restricted. Contact Admin', '');
                }
            } else {
                return $this->sendResponse(2, 401, 'Unautorized user. Logout Then Login again to continue', '');
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


   


   


    
}
