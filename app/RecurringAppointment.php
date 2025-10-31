<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecurringAppointment extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'appointment_id', 'week_days', 'recurs_every_weeks', 'first_appointment_date', 'last_appointment_date', 'never_ends', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'first_appointment_date' => 'date:d-m-Y',
        'last_appointment_date' => 'date:d-m-Y'
    ];
}
