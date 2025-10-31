<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientEnquiryLog extends Model
{
    protected $connection = 'second_db';
    protected $fillable = [
         'table_name', 'table_id', 'ip_address', 'description', 'before_update', 'after_update',  'user_id', 'updated_at', 'created_at', 'updated_by'
    ];
}
