<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisaType extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'visa_type', 'short_code', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
