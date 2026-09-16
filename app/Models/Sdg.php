<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sdg extends Model
{
    protected $fillable = [
        'number',
    ];

    // config/sdgs.php is the single source of truth for the official
    // name — previously only the submission form's picker component
    // knew these names, so nothing else (cards, the detail page,
    // reviewer views) could label a goal.
    public function getNameAttribute(): string
    {
        return config('sdgs.'.$this->number, 'SDG '.$this->number);
    }

    public function getLabelAttribute(): string
    {
        return "SDG {$this->number} – {$this->name}";
    }

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
