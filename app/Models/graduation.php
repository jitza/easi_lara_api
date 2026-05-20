<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class graduation extends Model
{
    protected $table = 'academics.graduations';
    protected $connection = 'pgsql';

    public $timestamps = false;

    protected $fillable = [
        'date',
        'description',
        'semesterId',
    ];


     public function graduationsmester()
    {
        return $this->belongsTo(semesters::class, 'semesterId', 'id');
    }
}
