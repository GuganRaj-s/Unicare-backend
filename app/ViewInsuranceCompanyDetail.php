<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewInsuranceCompanyDetail extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_insurance_company_details';

    protected $casts = [
        'start_date' => 'date:d-m-Y',
        'end_date' => 'date:d-m-Y',
        'form_type' => 'integer',
        'mediator_id' => 'integer',
        'min_limit' => 'integer',
        'max_limit' => 'integer',
        'claim_no' => 'integer',
        'outsource_lab' => 'integer',
        'e_auth' => 'integer',
        'activity_clinician' => 'integer',
        'eligiblity' => 'integer',
        'pharmacy_token' => 'integer',
        'no_lab_xml' => 'integer',
        'is_status' => 'integer'
    ];
}
