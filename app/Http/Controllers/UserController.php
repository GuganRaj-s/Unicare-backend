<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserLog;
use App\UserAuditLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use \Validator;
use \stdClass;
use DateTime;
use DateInterval;
use Illuminate\Support\Facades\Artisan;

class UserController extends Controller
{
    public function clearLaravelCache()
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        return response()->json(['status' => 1, 'message' => 'Config and cache cleared']);
    }
    public function Passwordlog(Request $request)
    {
        $user_id    = $_GET['uid'];
        $from_date  = $_GET['fdate'];
        $end_date   = $_GET['tdate'];

        $from_date = date("Y-m-d 00:00:00", strtotime($from_date)); 
        $end_date = date("Y-m-d 23:59:59", strtotime($end_date)); 

        //$logs =  DB::connection('second_db')->select("SELECT id,ip_address,after_update,user_id,created_at FROM user_audit_logs WHERE user_id = $user_id AND log_type = 'UserPass' AND created_at >= date('$from_date') AND created_at <= date('$end_date')  ORDER BY id DESC");

        $logs =  UserLog::SELECT('id','ip_address','user_id','created_at')
                ->WHERE(['user_id' => $user_id, 'action_type' => 'Password']) 
                ->whereBetween('created_at', [$from_date, $end_date])
                ->orderBy('id', 'DESC')
                ->limit(50)->get();

        return view('passlog',['logs' => $logs]);
    }


    public function Loginlog(Request $request)
    {
        $user_id    = $_GET['uid'];
        $from_date  = $_GET['fdate'];
        $end_date   = $_GET['tdate'];

        $from_date = date("Y-m-d 00:00:00", strtotime($from_date)); 
        $end_date = date("Y-m-d 23:59:59", strtotime($end_date)); 

        //$logs =  DB::connection('second_db')->select("SELECT id,ip_address,after_update,user_id,created_at FROM user_audit_logs WHERE user_id = $user_id AND log_type = 'UserPass' AND created_at >= date('$from_date') AND created_at <= date('$end_date')  ORDER BY id DESC");

        $logs =  UserAuditLog::SELECT('id','ip_address','before_update', 'after_update', 'updated_by', 'user_id')
                ->WHERE(['user_id' => $user_id, 'log_type' => 'UserLogin']) 
                ->whereBetween('created_at', [$from_date, $end_date])
                ->orderBy('id', 'DESC')
                ->limit(50)->get();

        return view('loginlog',['logs' => $logs]);
    }
}
