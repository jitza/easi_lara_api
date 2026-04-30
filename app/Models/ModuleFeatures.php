<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Module;
 class ModuleFeatures extends Model
 {
  protected $table = 'moduleFeatures';
    protected $primaryKey = 'id';
    public $timestamps = false;

   
    protected $fillable = [
        'featureName',
        'featureDescription',
        'moduleId',
    ];

    // Fix: Module not Modules
    public function module()
    {
        return $this->belongsTo(Module::class, 'moduleId', 'id');
    }

    public function roleModuleFeatures()
    {
        return $this->hasMany(RoleModuleFeatures::class, 'moduleFeatureId', 'id');
    }
}

// class ModuleFeatures extends Model
// {
//     protected $table = 'moduleFeatures';
//     protected $connection = 'pgsql';
//     public function module()
//     {
//        // return $this->belongsTo(Module::class, 'moduleId',);
//     return $this->belongsTo(Module::class, 'moduleId', 'id');
//        }
// }