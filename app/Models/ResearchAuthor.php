<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One researcher entry on a submission (stud_file_author). May or may not
 * be linked to a real account — user_id is nullable, and author_name is
 * always populated (a snapshot of the linked user's name at submit time,
 * or a free-typed name for a "guest" researcher with no account).
 */
class ResearchAuthor extends Model
{
    protected $table = 'stud_file_author';

    protected $fillable = [
        'stud_file_id',
        'user_id',
        'author_name',
        'role_label',
        'position',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studFile()
    {
        return $this->belongsTo(UserFile::class, 'stud_file_id');
    }
}
