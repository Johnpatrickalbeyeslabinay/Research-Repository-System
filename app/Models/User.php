<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'user_id',
        'role',
        'name',
        'year_level',
        'course',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

}