<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentMode extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
