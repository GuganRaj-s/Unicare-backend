<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\EmirateMaster;
use App\ViewEmirateMaster;
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

class EmirateMasterController extends BaseController
{

    
    public function AddEmirateDeatil(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'emirate_id'        => 'required|string|min:3|max:50',
                'full_name'         => 'required|string',
                'mobile'            => 'nullable|min:3|max:30',
                'emirate_data'      => 'required|array',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }
            
            $master = EmirateMaster::create([
                'full_name' => $request->full_name,
                'emirate_ids' => $request->emirate_id,
                'mobile' => $request->mobile,
                'emirate_data' => json_encode($request->emirate_data),
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($master->id) {
                return $this->sendResponse(1,200, 'Emirate details added successfully');
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API AddEmirateDeatil:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleEmirateDeatil(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id',
                'emirate_id'        => 'required|string|min:3|max:50',
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $emirate = ViewEmirateMaster::SELECT('emirate_ids', 'full_name', 'mobile', 'emirate_data')
                    ->where(['emirate_ids' => $request->emirate_id])
                    ->orderBy('id', 'desc')->limit(1)->get();
            if($emirate->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $emirate);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $emirate->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSinglePatientEnquiry :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


}