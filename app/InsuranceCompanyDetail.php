<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InsuranceCompanyDetail extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'insurance_company_detail_id', 'form_type', 'name', 'short_code', 'payer_ids', 'receiver_ids', 'provider_code', 'company_type_id', 'charge_type_id', 'start_date', 'end_date', 'min_limit', 'max_limit', 'claim_no', 'outsource_lab', 'e_auth', 'activity_clinician', 'eligiblity', 'pharmacy_token', 'no_lab_xml', 'contact_person','designation','department','contact_phone','contact_mobile','contact_fax','contact_email','address','country_id','region_id','city_id','pincode','billing_phone','billing_fax','website','is_status','benefit_package', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'start_date' => 'date:d-m-Y',
        'end_date' => 'date:d-m-Y',
        'form_type' => 'integer',
        'mediator_id' => 'integer',
        'min_limit' => 'integer',
        'max_limit' => 'integer',
        'claim_no' => 'integer',
        'outsource_lab' => 'integer',
        'benefit_package' => 'integer',
        'e_auth' => 'integer',
        'activity_clinician' => 'integer',
        'eligiblity' => 'integer',
        'pharmacy_token' => 'integer',
        'no_lab_xml' => 'integer',
        'is_status' => 'integer'
    ];
}
