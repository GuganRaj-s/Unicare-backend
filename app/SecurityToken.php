<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SecurityToken extends Model
{
    protected $fillable = [
        'mobile', 'token','created_at', 'otp_code'
    ];
}
