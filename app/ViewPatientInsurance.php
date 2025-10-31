<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewPatientInsurance extends Model
{
    protected $connection = 'mysql';
    public $table = "view_patient_insurances"; 
    protected $casts = [
        'expiry_date' => 'date:d-m-Y'
    ];

    public function insurance()
    {
        return $this->hasMany('App\ViewPatientInsurance', 'patient_detail_id', 'id');
    }

    public function insurance_detail()
    {
        return $this->hasMany('App\ViewPatientInsuranceDetail',  'patient_insurance_id', 'id');
    }
}
