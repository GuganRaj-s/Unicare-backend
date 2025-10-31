<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CancelReason extends Model
{
    protected $connection = 'mysql';
    protected $table = 'tbl_cancelreason';
    protected $fillable = [
        'name', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
