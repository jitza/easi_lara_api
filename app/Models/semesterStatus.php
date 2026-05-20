<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class semesterStatus extends Model
{
   protected $table = 'academics.semesterStatus';
    protected $connection = 'pgsql';

    public $timestamps = false;

    protected $fillable = [
        'status',

        
    ];

        public function semesterstatus()
        {
            return $this->hasOne(Semesters::class, 'statusId', 'id');
        } 


}
