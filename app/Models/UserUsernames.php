<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class  UserUsernames extends Model
{
    protected $table = 'personnel.user_usernames';
    protected $connection = 'pgsql';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'username'
    ];

    // public function user_usernames()
    //     {
    //         return $this->hasOne(user_usernames::class, 'username', 'username');
    //     } 
}
