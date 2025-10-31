<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewPatientGuardian extends Model
{
    protected $connection = 'mysql';
    public $table = "view_patient_guardians";    
}
