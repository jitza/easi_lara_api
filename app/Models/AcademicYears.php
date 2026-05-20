<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYears extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'academics.academicYears';
    

    public $timestamps = false;

    protected $fillable = [
        
        'startDate',
        'endDate',
        'code',
        'name',
        
    ];
}
