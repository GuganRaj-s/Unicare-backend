<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientDemoGraphic extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'patient_detail_id', 'ethnic_id', 'language_id', 'education_id', 'occupation_id', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
