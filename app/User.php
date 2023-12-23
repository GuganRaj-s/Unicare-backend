<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'email', 'password', 'user_identifier', 'role_id', 'parent_id', 'username', 'mobile', 'doj', 'dob', 'gender', 'deposit_amount', 'subdistributor_fees','retailer_fees', 'gst_number','pan_no', 'aadhar_no','agency_name', 'address','allowed_device', 'client_limit','retailer_margin_limit', 'payment_request','money_request', 'profile_image', 'allowed_ip', 'user_status', 'distributor_id', 'subdistributor_id', 'is_active', 'created_by', 'updated_by', 'api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

}
