<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'excluded_from_history',
        'citations',
        'external_sources',
        'retrieval_meta',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'latency_ms',
    ];

    protected $casts = [
        'citations' => 'array',
        'external_sources' => 'array',
        'retrieval_meta' => 'array',
        'excluded_from_history' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }
}
