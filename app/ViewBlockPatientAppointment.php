<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewBlockPatientAppointment extends Model
{
    protected $connection = 'mysql';
    public $table = "view_block_patient_appointments";
}