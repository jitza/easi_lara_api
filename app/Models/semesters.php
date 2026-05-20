<?php

namespace App\Models;
use App\Models\AcademicYears;
use App\Models\SemesterStatus;

use Illuminate\Database\Eloquent\Model;

class semesters extends Model
{
    protected $table = 'academics.semesters';
    protected $connection = 'pgsql';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'academicYearId',
        'dateStarted',
        'dateEnd',
        'statusId',

        
    ];
// defines relationships with id to get necessary data 

    public function academicYear()
    {
        return $this->belongsTo(AcademicYears::class, 'academicYearId', 'id');
    }

    public function status()
    {
        return $this->belongsTo(SemesterStatus::class, 'statusId', 'id');
    }

    public function graduationsemester()
    {
        return $this->hasOne(graduations::class, 'semesterId', 'id');
    } 
}