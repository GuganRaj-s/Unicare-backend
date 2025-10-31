<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlockTempAppointment extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'hospital_detail_id', 'user_id', 'start_date', 'end_date', 'start_time', 'end_time', 'is_recursive', 'start_timestamp', 'end_timestamp', 'remarks', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'start_datetime' => 'date:d-m-Y H:i:s',
        'end_datetime' => 'date:d-m-Y H:i:s'
    ];
}
