<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewWaitingList extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_waiting_lists';
}
