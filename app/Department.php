<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'dept_catgory_id', 'short_code', 'srvc_cat_code', 'malaffi_code', 'malaffi_specialty', 'malaffi_dept_name', 'appointment', 'is_hl', 'is_revenue', 'is_sms', 'suppress', 'supact', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
