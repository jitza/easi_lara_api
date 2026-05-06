<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Roles;

class UserModuleFeatures extends Model
{
    protected $table = 'userModuleFeatures';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'userAccountId',
        'roleId',
        'moduleFeatureId',
        'moduleFeatureEnabled',
    ];

public function userModuleFeatures()
{
    return $this->hasMany(UserModuleFeatures::class, 'roleId', 'id');
}

public function role()
{
    return $this->belongsTo(Roles::class, 'roleId', 'id');
}

}