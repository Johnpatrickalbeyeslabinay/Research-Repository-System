<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ResearchFeedback;


class UserFile extends Model
{
    use HasFactory;

    protected $table = 'stud_file';

    protected $fillable = [
        'user_id',
        'title',
        'name',
        'adviser',
        'adviser_id',
        'desc',
        'file_path',
        'auto_tag',
        'views',
        'downloads',
        'status',
        'visibility',
        'requested_visibility',
        'research_type',
        'publication_year',
        'pdf_hash',
        'pdf_page_count',
        'extracted_abstract',
        'abstract_source',
        'extracted_sections',
        'ai_analysis_status',
        'ai_analysis_fingerprint',
        'ai_suggested_keywords',
        'ai_suggested_sdgs',
        'ai_model_used',
        'ai_analyzed_at',
        'search_index_status',
        'search_indexed_at',
        'search_index_error',
        'instructor_feedback',
    ];

    protected $casts = [
        'extracted_sections' => 'array',
        'ai_suggested_keywords' => 'array',
        'ai_suggested_sdgs' => 'array',
        'ai_analyzed_at' => 'datetime',
        'search_indexed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adviserUser()
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    public function sdgs()
    {
        return $this->belongsToMany(
            Sdg::class,
            'stud_file_sdg',
            'stud_file_id',
            'sdg_id'
        );
    }

    public function keywords()
    {
        return $this->belongsToMany(
            Keyword::class,
            'stud_file_keyword',
            'stud_file_id',
            'keyword_id'
        )->withPivot('source');
    }

    // Ordered list of researchers on this submission — linked to a user
    // account or not (a free-typed "guest" name). See ResearchAuthor;
    // author_name is always populated regardless of user_id.
    public function authors()
    {
        return $this->hasMany(ResearchAuthor::class, 'stud_file_id')->orderBy('position');
    }

    /**
     * The complete researcher list: the submitter (recorded on stud_file
     * itself via user_id/name, never as a stud_file_author row — see
     * StudentController::syncAuthors) followed by everyone added through
     * the researcher picker, whether linked to an account or a free-typed
     * guest.
     *
     * Every surface that shows authorship must use this. Rendering
     * `$file->name` alone shows only the submitter and silently hides
     * every co-author — which is what all five views used to do.
     *
     * Callers should eager-load `authors` or this N+1s.
     *
     * @return string[]
     */
    public function researcherNames(): array
    {
        return array_values(array_unique(array_filter(array_merge(
            [$this->name],
            $this->authors->pluck('author_name')->all()
        ))));
    }

    public function chunks()
    {
        return $this->hasMany(ResearchChunk::class);
    }

    // Single-research chatbot conversations pinned to this document (see
    // research/show's "Ask about this research").
    public function chatConversations()
    {
        return $this->hasMany(ChatConversation::class, 'stud_file_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(
            ResearchFeedback::class,
            'stud_file_id'
        )->with('user')->latest();
    }
}
