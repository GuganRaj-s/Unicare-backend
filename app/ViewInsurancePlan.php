<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewInsurancePlan extends Model
{
    protected $connection = 'mysql';
    public $table = "view_insurance_plans";
}
