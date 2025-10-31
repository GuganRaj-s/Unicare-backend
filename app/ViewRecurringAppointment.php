<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewRecurringAppointment extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_recurring_appointments';
}
