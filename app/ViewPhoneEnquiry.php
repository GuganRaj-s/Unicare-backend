<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewPhoneEnquiry extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_phone_enquiry';

    protected $casts = [
        'appointment_date' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y h:i:s'
    ];
}
