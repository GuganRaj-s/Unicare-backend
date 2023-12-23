<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\User;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});



Route::post('v1/login', 'API\UserController@login');
Route::resource('v1', UserController::class);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('user/GetProfile', 'API\UserController@GetProfile');
    Route::resource('user', UserController::class);

});
