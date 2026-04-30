<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class roleModuleFeatures extends Model
{
    protected $table = 'roleModuleFeatures';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'roleId',
        'moduleFeatureId',
        'moduleFeatureEnabled',
    ];

    // Relationship to role
    public function role()
    {
        return $this->belongsTo(Roles::class, 'roleId', 'id');
    }

    // Relationship to moduleFeature
    public function moduleFeature()
    {
        return $this->belongsTo(ModuleFeatures::class, 'moduleFeatureId', 'id');
    }
}
