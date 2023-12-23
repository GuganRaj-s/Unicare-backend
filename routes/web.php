<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return View::make('home');
})->name('home');


// Route::get('/forgot-password', function() {
//     return redirect('/forgot');
// });

Route::get('/clear-cache', function() {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
 
    return "Cleared!";
 
});


Route::post('postSignIn', 'UserController@postSignIn')->name('login.postSignIn');


Route::get('logout', 'UserController@logout')->name('user.logout');

Auth::routes(['register' => false]);

Route::middleware(['auth'])->group(function () {

  
});

//Route::get('/home', 'HomeController@index')->name('home');
