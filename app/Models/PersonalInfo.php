<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalInfo extends Model
{
    protected $table = 'personnel.personalInfo';
    protected $connection = 'pgsql';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'firstName',
        'middleName',
        'lastName',
        'dateOfBirth',
        'gender',
        'socialSecurityNumber',
        'homePhone',
        'mobilePhone',
        'email',
        'citizenshipStatus',
        
    ];

   public function userAccount()
{
    return $this->hasOne(UserAccountsModel::class, 'personId', 'id');
} 
}