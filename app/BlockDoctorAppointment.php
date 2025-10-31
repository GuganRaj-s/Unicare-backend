<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlockDoctorAppointment extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'hospital_detail_id', 'user_id', 'start_date', 'end_date', 'start_time', 'end_time', 'is_recursive', 'start_timestamp', 'end_timestamp', 'remarks', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'start_date' => 'date:d-m-Y',
        'end_date' => 'date:d-m-Y',
        'start_time' => 'date:H:i:s',
        'end_time' => 'date:H:i:s'
    ];
}
