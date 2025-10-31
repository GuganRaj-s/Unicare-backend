<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewAppointment extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_appointments';
}
