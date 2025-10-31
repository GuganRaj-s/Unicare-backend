<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewEmirateMaster extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_emirate_masters';

    protected $casts = [
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A'
    ];
}
