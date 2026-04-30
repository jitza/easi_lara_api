<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserModuleFeatures;

class roles extends Model
{ 
    protected $table = 'roles';
  
    protected $primaryKey = 'id';
    public $timestamps = false;

   protected $fillable = [
    'id',
    'rolename',
    'roledescription',
    'status',
    ];  

    // Relationship to roleModuleFeatures
    public function roleModuleFeatures()
    {
        return $this->hasMany(RoleModuleFeatures::class, 'roleId', 'id');
    }

    // Relationship to userModuleFeatures
    public function userModuleFeatures()
    {
        return $this->hasMany(UserModuleFeatures::class, 'roleId', 'id');
    }
}