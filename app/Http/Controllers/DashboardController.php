<?php

namespace App\Http\Controllers;
use App\Operator;
use App\ViewTransaction;
use App\ViewUserBalance;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\TransactionController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user_id = auth()->user()->id;
        $user_controller   = new UserController;
        return view('dashboard.index');
    }


    public function profile(Request $request)
    {
        //dd("working");
        return view('dashboard.profile');
    }

    


}
