<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $fillable = [
        'name',
        'display_name',
    ];

    public function userFiles()
    {
        return $this->belongsToMany(
            UserFile::class,
            'stud_file_keyword',
            'keyword_id',
            'stud_file_id'
        )->withPivot('source');
    }

    /**
     * Find (or create) the keyword row for a given display string, keyed
     * off a normalized lowercase name so "Artificial Intelligence" and
     * "artificial intelligence" resolve to the same row.
     */
    public static function findOrCreateByDisplayName(string $displayName): self
    {
        $displayName = trim(preg_replace('/\s+/', ' ', $displayName));
        $normalized = mb_strtolower($displayName);

        return static::firstOrCreate(
            ['name' => $normalized],
            ['display_name' => $displayName]
        );
    }
}
