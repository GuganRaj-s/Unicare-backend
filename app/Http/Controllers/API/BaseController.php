<?php
namespace App\Http\Controllers\API;
use App\UserAuditLog;
use App\MasterLog;
use App\User;
use App\ViewSubModulePermission;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($status, $code, $message, $parameter = null, $result = [])
    {
    	$response = [
            'status' => $status,
            'message' => $message,
        ];

        if(!empty($result)){
            $response[$parameter] = $result;
        }

        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($status, $message, $errorMessages = [])
    {
    	$response = [
            'status' => $status,
            'message' => $message,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, 404);
    }

    public function CryptoEncryption($input) {
        $saltCiphertext = base64_decode($input);

        $salt = substr($saltCiphertext, 8, 8);
        $ciphertext = substr($saltCiphertext, 16);


        // Separate key and IV
        $keyIv = $this->EVP_BytesToKey($salt, env('ENC_SALT_KEY'));
        $key = substr($keyIv, 0, 32);
        $iv = substr($keyIv, 32, 16);

        // Decrypt using key and IV
        $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        // while ($msg = openssl_error_string())
        // echo $msg . "<br />\n";

        return json_decode($decrypted, true);

    }

    public function EVP_BytesToKey($salt, $password) {
	    $bytes = ''; 
	    $last = '';
	    while(strlen($bytes) < 48) {
	        $last = hash('md5', $last . $password . $salt, true);
	        $bytes.= $last;
	    }
	    return $bytes;
	}	

    public function UserAuditLogSave($user_id, $log_type, $before, $after, $username) {
        $ip = \Request::ip();
        $logs = UserAuditLog::create([
            'log_type'=>$log_type,
            'ip_address'=>$ip,
            'updated_by'=>$username,
            'before_update'=>$before,
            'after_update'=>$after,
            'user_id'=>$user_id,
            'updated_at'=>date('Y-m-d h:i:s')
        ]);

        return $logs->id;
    }

    public function UserAuditLogUpdate($user_id, $log_id) {
        $username = $this->GetUsername($user_id);
        $logs = UserAuditLog::find($log_id);
        $logs->updated_by = $username;
        $logs->after_update = date('Y-m-d h:i:s');
        $logs->updated_at = date('Y-m-d h:i:s');
        $logs->update();

        return true;
    }

    public function UpdateLogNewChanges($user_id, $table_id, $table_name, $logs = []) {
        $username = $this->GetUsername($user_id);
        if(!empty($logs)) {
            foreach($logs as $log) {
                $field_name = $log['field_name'];
                $old_value  = $log['old_value'];
                $new_value  = $log['new_value'];

                $ip = \Request::ip();
                $logs = MasterLog::create([
                    'table_name'=>$table_name,
                    'table_id' => $table_id,
                    'ip_address'=>$ip,
                    'before_update'=>$old_value,
                    'after_update'=>$new_value,
                    'description' => 'Updated '.$field_name.' FROM '.$old_value.' TO '.$new_value,
                    'user_id'=>$user_id,
                    'updated_by'=>$username,
                    'updated_at'=>date('Y-m-d h:i:s')
                ]);

            }
            return true;
        }
        return true;
    }

    public function VerifyAuthUser($user_id, $role_id) {
        try {
            $user = auth()->user(); 
            if(!empty($user)) {
                if($user->id == $user_id) {
                    if($role_id != 0 ) {
                        if($user->role_id == $role_id || $user->role_id == 1) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                    return true;
                } else { 
                    return false;
                }
            } else {
                return false;
            }
        } catch(\Exception $e) {
            return false;
        }
    }

    public function GetUserRole($user_id) {
        try {
            $user = auth()->user(); 
            if(!empty($user)) {
                if($user->id == $user_id) {
                    return $user->role_id;
                } else { 
                    return false;
                }
            } else {
                return false;
            }
        } catch(\Exception $e) {
            return false;
        }
    }

    public function VerifyPageAccess($page, $action) {
        try {
            $user = auth()->user(); 
            if(!empty($user)) {
                $role_id = $user->role_id;
                $access = ViewSubModulePermission::where(['sub_role_id'=>$role_id, 'sub_menu_link'=>$page,  $action=>1])->get();
                if($access->count() == 1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch(\Exception $e) {
            return false;
        }
    }

    public function DeleteUserTokens($mobile) {
        try {

            DB::table('security_tokens')->where(['mobile'=> $mobile])->delete();
            return true;

        } catch(\Exception $e) {
            Log::debug($e->getMessage());
            return false;
        }
    }

    public function GetUsername($user_id) {
        $user = User::SELECT('id', 'full_name')->WHERE(['id' => $user_id])->get();
        if(!empty($user)) {
            return $user[0]->full_name;
        } else {
            return '';
        }
    }

    public function UpdateLogs($user_id, $table_id, $table_name, $referral_table,  $old_array = [], $new_array = [], $field_names = []) {
        $username = $this->GetUsername($user_id);
        if(!empty($old_array)) {
            $length = count($field_names);
            $keys = array_keys($field_names);
            $ip = \Request::ip();
            $model_name = '\\App\\'.$table_name;
            $model = new $model_name;
            for($i = 0; $i < $length; $i++){
                $key_name   = $keys[$i];
                $key_value  = $field_names[$key_name];
                $new_value  = $new_array->$key_name;
                $old_value  = $old_array[0]->$key_name;
                if($new_value != $old_value) {
                    $logs = $model::create([
                        'table_name'=>$referral_table,
                        'table_id' => $table_id,
                        'action_type' => 'Update',
                        'ip_address'=>$ip,
                        'before_update'=>$old_value,
                        'after_update'=>$new_value,
                        'description' => $key_value.' FROM '.$old_value.' TO '.$new_value,
                        'user_id'=>$user_id,
                        'updated_by'=>$username,
                        'updated_at'=>date('Y-m-d h:i:s')
                    ]);
                }
            }
            return true;
        }
        return true;
    }

    public function DeleteLogs($user_id, $table_id, $table_name, $referral_table, $description) {
        $username = $this->GetUsername($user_id);
        $ip = \Request::ip();
        $model_name = '\\App\\'.$table_name;
        $model = new $model_name;
        $logs = $model::create([
            'table_name'=>$referral_table,
            'table_id' => $table_id,
            'action_type' => 'Delete',
            'ip_address'=>$ip,
            'before_update'=> 'Active',
            'after_update'=> 'In-Active',
            'description' => $referral_table. ' table record deleted. Reference ID is '.$table_id,
            'user_id'=>$user_id,
            'updated_by'=>$username,
            'updated_at'=>date('Y-m-d h:i:s')
        ]);
    }

    public function UpdateSpecificFieldLog($user_id, $table_id, $table_name, $referral_table, $old_value,  $new_value, $field_name, $description, $action) {
        $username = $this->GetUsername($user_id);
        $ip = \Request::ip();
        $model_name = '\\App\\'.$table_name;
        $model = new $model_name;
        $logs = $model::create([
            'table_name'=>$referral_table,
            'table_id' => $table_id,
            'action_type' => $action,
            'ip_address'=>$ip,
            'before_update'=> $old_value,
            'after_update'=> $new_value,
            'description' => $description.' FROM '.$old_value.' TO '.$new_value,
            'user_id'=>$user_id,
            'updated_by'=>$username,
            'updated_at'=>date('Y-m-d h:i:s')
        ]);
    }


}