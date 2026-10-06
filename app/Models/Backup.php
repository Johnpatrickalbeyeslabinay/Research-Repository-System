<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = [
        'name',
        'type',
        'file_name',
        'file_path',
        'file_size',
        'records_count',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'records_count' => 'integer',
    ];
}
