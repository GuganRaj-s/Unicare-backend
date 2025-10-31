<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewPatientEnquiry extends Model
{
    protected $connection = 'mysql';
    public $table = "view_patient_enquiry";   

    protected $casts = [
        'appointment_date' => 'date:d-m-Y'
    ];
}
