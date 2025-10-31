<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Demo extends Model
{
    protected $connection = 'mysql';
   /* protected $fillable = [
        'schedule_id', 'resource_type', 'patient_name', 'service_type', 'specialty', 'created_at', 'updated_at', 'appointment_date', 'start_time', 'end_time'
    ]; */
    
    protected $fillable = [
        'param_1',
        'param_2',
        'param_3',
        'param_4',
        'param_5',
        'param_6',
        'param_7',
        'param_8',
        'param_9',
        'param_10',
        'param_11',
        'param_12',
        'param_13',
        'param_14',
        'param_15',
        'param_16',
        'param_17',
        'param_18',
        'param_19',
        'param_20',
        'param_21',
        'param_22',
        'param_23',
        'param_24',
        'param_25',
        'param_26',
        'param_27',
        'param_28',
        'param_29',
        'param_30', 
        'created_at', 
        'updated_at', 
    ];
    
    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];
    
}
