<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sdg extends Model
{
    protected $fillable = [
        'number',
    ];

    public function userFiles()
{
    return $this->belongsToMany(
        UserFile::class,
        'stud_file_sdg',
        'sdg_id',
        'stud_file_id'
    );
}
}
