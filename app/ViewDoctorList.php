<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewDoctorList extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_doctor_lists';

    protected $casts = [
        'shift_start_date' => 'date:d-m-Y',
        'shift_end_date' => 'date:d-m-Y',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A'
    ];
}
