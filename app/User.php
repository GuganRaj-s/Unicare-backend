<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable,HasApiTokens;

    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'first_name', 'middle_name', 'last_name', 'full_name', 'email', 'password', 'username', 'role_id', 'branch_id', 'mobile', 'emp_doj', 'emp_out', 'gender_id', 'department_id', 'job_title_id','city_id', 'region_id','country_id', 'address_line1','address_line2', 'profile_img','digital_signature', 'user_status','account_status', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by', 'api_token'
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
