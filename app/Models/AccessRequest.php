<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessRequest extends Model
{
    protected $table = 'research_access_requests';

    protected $fillable = [
        'stud_file_id',
        'email',
        'reason_access',
        'status',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(UserFile::class, 'stud_file_id');
    }
}
