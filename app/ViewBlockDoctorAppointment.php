<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewBlockDoctorAppointment extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_block_doctor_appointments';

    protected $casts = [
        'start_datetime' => 'date:d-m-Y H:i:s',
        'end_datetime' => 'date:d-m-Y H:i:s',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A'
    ];
}
