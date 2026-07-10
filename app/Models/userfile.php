<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserFile extends Model
{
    use HasFactory;

    protected $table = 'stud_file';

    protected $fillable = [
        'user_id',
        'title',
        'name',
        'adviser',
        'desc',
        'file_path',
        'auto_tag',
        'downloads',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}