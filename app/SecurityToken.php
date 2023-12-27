<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SecurityToken extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'mobile', 'token','created_at', 'otp_code'
    ];
}
