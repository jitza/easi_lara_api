<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\PersonalInfo;
use App\Models\UserUsernames;

class UserAccountsModel extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'userAccounts';
    protected $connection = 'pgsql';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'username',
        'password',
        'status',
        'personId',
        'activeUntil',
        'accessibleFrom'
    ];

    protected $hidden = [
        'password',
    ];

  public function moduleFeatures()
    {
        return $this->belongsToMany(
            ModuleFeatures::class,
            'userModuleFeatures',
            'userAccountId',
            'moduleFeatureId'
        )->withPivot('moduleFeatureEnabled');
    }


    public function personalInfo()
    {
        return $this->belongsTo(PersonalInfo::class, 'personId', 'id');
    }


    public function user_usernames()
    {
        return $this->hasOne(UserUsernames::class, 'username', 'username');
    }

    public function userModuleFeatures()
    {
        return $this->hasMany(UserModuleFeatures::class, 'userAccountId', 'id');
    }

}