<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralSource extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
