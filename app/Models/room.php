<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class room extends Model
{
   
    protected $table = 'organizationLandscape.rooms';
    protected $connection = 'pgsql';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'buildingId',
        'code',
       
        
    ];
}
