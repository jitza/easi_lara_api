<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class building extends Model
{
    protected $table = 'organizationLandscape.buildings';
    protected $connection = 'pgsql';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
       
        
    ];

     public function rooms()
    {
        return $this->hasMany(Room::class, 'buildingId');
    }
}
