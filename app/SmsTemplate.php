<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
         'name', 'short_code', 'english_message', 'arabic_message', 'is_active',  'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
}
