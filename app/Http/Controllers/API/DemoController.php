<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\Demo;
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

class DemoController extends BaseController
{

    public function GetAllSchedule(Request $request) {
        try {

            $schedule = Demo::SELECT('id', 'param_1',
        'param_2',
        'param_3',
        'param_4',
        'param_5',
        'param_6',
        'param_7',
        'param_8',
        'param_9',
        'param_10',
        'param_11',
        'param_12',
        'param_13',
        'param_14',
        'param_15',
        'param_16',
        'param_17',
        'param_18',
        'param_19',
        'param_20',
        'param_21',
        'param_22',
        'param_23',
        'param_24',
        'param_25',
        'param_26',
        'param_27',
        'param_28',
        'param_29',
        'param_30', 'created_at', 'updated_at')
                        ->WHERE(['is_active' => 1])
                        ->ORDERBY('id', 'DESC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $schedule);
            
        } catch(\Exception $e) {
            Log::debug("API GetAllSchedule:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function CreatedSchedule(Request $request) {
        try {
            /*$validator = Validator::make($request->all(), [
                'schedule_id'        => 'required',
                'resource_type'      => 'required',
                'patient_name'       => 'required|string|min:3|max:30',
                'service_type'       => 'required',
                'specialty'          => 'required',
                'appointment_date'   => 'required|date', 
                'start_time'         => 'required',
                'end_time'           => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            $appointment_date = null; 
            if($request->appointment_date != ''){
                $appointment_date = date("Y-m-d", strtotime($request->appointment_date)); 
            } */

            $patient = Demo::create([
                'param_1' => (isset($request->param_1) ? $request->param_1 : '' ),
                'param_2' => (isset($request->param_2) ? $request->param_2 : '' ),
                'param_3' => (isset($request->param_3) ? $request->param_3 : '' ),
                'param_4' => (isset($request->param_5) ? $request->param_5 : '' ),
                'param_5' => (isset($request->param_5) ? $request->param_5 : '' ),
                'param_6' => (isset($request->param_6) ? $request->param_6 : '' ),
                'param_7' => (isset($request->param_7) ? $request->param_7 : '' ),
                'param_8' => (isset($request->param_8) ? $request->param_8 : '' ),
                'param_9' => (isset($request->param_9) ? $request->param_9 : '' ),
                'param_10' => (isset($request->param_10) ? $request->param_10 : '' ),
                'param_11' => (isset($request->param_11) ? $request->param_11 : '' ),
                'param_12' => (isset($request->param_12) ? $request->param_12 : '' ),
                'param_13' => (isset($request->param_13) ? $request->param_13 : '' ),
                'param_14' => (isset($request->param_14) ? $request->param_14 : '' ),
                'param_15' => (isset($request->param_15) ? $request->param_15 : '' ),
                'param_16' => (isset($request->param_16) ? $request->param_16 : '' ),
                'param_17' => (isset($request->param_17) ? $request->param_17 : '' ),
                'param_18' => (isset($request->param_18) ? $request->param_18 : '' ),
                'param_19' => (isset($request->param_19) ? $request->param_19 : '' ),
                'param_20' => (isset($request->param_20) ? $request->param_20 : '' ),
                'param_21' => (isset($request->param_21) ? $request->param_21 : '' ),
                'param_22' => (isset($request->param_22) ? $request->param_22 : '' ),
                'param_23' => (isset($request->param_23) ? $request->param_23 : '' ),
                'param_24' => (isset($request->param_24) ? $request->param_24 : '' ),
                'param_25' => (isset($request->param_25) ? $request->param_25 : '' ),
                'param_26' => (isset($request->param_26) ? $request->param_26 : '' ),
                'param_27' => (isset($request->param_27) ? $request->param_27 : '' ),
                'param_28' => (isset($request->param_28) ? $request->param_28 : '' ),
                'param_29' => (isset($request->param_29) ? $request->param_29 : '' ),
                'param_30' => (isset($request->param_30) ? $request->param_30 : '' ), 
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($patient->id) {
                return $this->sendResponse(1,200, 'Schedlue created successfully');
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }

        } catch(\Exception $e) {
            Log::debug('API CreatedSchedule :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

}