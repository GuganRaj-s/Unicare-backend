<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppointmentStatus extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
         'name', 'bg_color', 'font_color', 'malaffi_status', 'malaffi_description', 'booking_order', 'is_active',  'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
