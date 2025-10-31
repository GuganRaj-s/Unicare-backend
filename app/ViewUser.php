<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewUser extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_users';

    protected $casts = [
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A'
    ];
}
