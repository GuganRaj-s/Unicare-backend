<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftMaster extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'shift_name', 'start_time', 'end_time', 'total_hours', 'start_date', 'end_date', 'work_session', 'is_enabled', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
