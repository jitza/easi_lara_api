<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

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
}
