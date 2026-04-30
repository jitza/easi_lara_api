<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';
    protected $connection = 'pgsql';
    public function features()
    {
        return $this->hasMany(ModuleFeatures::class, 'moduleId');
    }
}