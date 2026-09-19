<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // database/factories/UserFactory.php already exists (with admin()/
    // student()/instructor() states used by the Chat feature tests) but was
    // unreachable without this trait — UserFile already had it.
    use HasFactory;
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
