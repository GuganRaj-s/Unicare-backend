<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegComments extends Model
{
    protected $connection = 'mysql';
    protected $table = 'tbl_RegComments';
    protected $fillable = [
        'patient_detail_id', 'DateTime', 'narrated_by', 'comments', 'alertType', 'blockAppt', 'doctor_id', 'patient_type', 'cancel', 'cancelled_at', 'cancelled_by', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
