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
        'citations',
        'retrieval_meta',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'latency_ms',
    ];

    protected $casts = [
        'citations' => 'array',
        'retrieval_meta' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }
}
