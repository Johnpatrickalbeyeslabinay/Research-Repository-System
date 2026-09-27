<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResearchFeedback extends Model
{
    use HasFactory;

    protected $table = 'research_feedback';

    protected $fillable = [
        'stud_file_id',
        'user_id',
        'feedback',
    ];

    public function file()
    {
        return $this->belongsTo(UserFile::class, 'stud_file_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
