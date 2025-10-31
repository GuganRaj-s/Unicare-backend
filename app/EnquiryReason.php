<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EnquiryReason extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'id', 'name', 'is_active', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
