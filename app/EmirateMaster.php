<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmirateMaster extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'full_name', 'emirate_ids', 'mobile', 'emirate_data', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
