<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Sdg extends Model
{
    protected $fillable = [
        'number',
    ];

    /**
     * All 17 goals, ordered — seeded reference data that every submission
     * form and repository filter renders, so it's served from cache rather
     * than costing a round trip to the remote database on each page view.
     * An empty result isn't cached, so a deploy that seeds after the first
     * request doesn't pin an empty picker for a day.
     */
    public static function cachedList(): Collection
    {
        $cached = Cache::get('sdgs:list');

        if ($cached instanceof Collection && $cached->isNotEmpty()) {
            return $cached;
        }

        $sdgs = static::orderBy('number')->get();

        if ($sdgs->isNotEmpty()) {
            Cache::put('sdgs:list', $sdgs, now()->addDay());
        }

        return $sdgs;
    }

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
