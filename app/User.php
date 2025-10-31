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
         'id', 'title_id', 'first_name', 'middle_name', 'last_name', 'full_name', 'current_email', 'current_mobile', 'current_phone', 'username', 'password', 'hospital_detail_id', 'department_id', 'role_id', 'job_title_id', 'current_city_id', 'current_region_id', 'current_country_id', 'gender_id', 'nationality_id', 'current_address_line', 'perm_address_line', 'emp_doj', 'emp_out', 'appt_interval', 'selected_color', 'view_other_doctor_appt', 'view_in_appt', 'short_name', 'dob', 'marital_status_id', 'religion_id', 'father_or_Husband', 'doc_qualification', 'doc_profile_id', 'current_address_postbox', 'perm_address_postbox', 'perm_country_id', 'perm_city_id', 'perm_region_id', 'perm_email', 'perm_mobile', 'perm_phone', 'emp_code', 'contract_no', 'offer_no', 'location_id', 'visa_type_id', 'ledger_no', 'grade', 'sponsor', 'accessCard_no', 'scheduler_access', 'insurance_card', 'accomodation', 'mother_name', 'qualification_id', 'profile_img', 'signature1', 'signature2', 'signature3', 'first_login', 'user_status', 'account_status', 'is_active', 'api_token', 'created_at', 'created_by', 'updated_at', 'updated_by'
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
