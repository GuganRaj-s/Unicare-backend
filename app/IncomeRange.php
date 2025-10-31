<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncomeRange extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'income_range', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
