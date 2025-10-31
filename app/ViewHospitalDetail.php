<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewHospitalDetail extends Model
{
    protected $connection = 'mysql';
    public $table = 'view_hospital_details';

    protected $casts = [
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A'
    ];
}
