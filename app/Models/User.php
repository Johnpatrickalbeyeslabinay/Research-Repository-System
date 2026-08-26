<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'user_id',
        'email',
        'role',
        'name',
        'year_level',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
