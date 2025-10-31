<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewAdminAccess extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_admin_accesses';
}
