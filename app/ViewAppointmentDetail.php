<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewAppointmentDetail extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_appointment_details';

    protected $casts = [
        'date_time' => 'date:d-m-Y h:i:s',
    ];
}
