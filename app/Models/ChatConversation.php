<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    protected $fillable = [
        'user_id',
        'stud_file_id',
        'title',
        'active_context',
        'message_count',
        'last_message_at',
    ];

    protected $casts = [
        'active_context' => 'array',
        'last_message_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Set only for a single-research chat (research/show's "Ask about this
    // research") — retrieval is pinned to this document. Null for a
    // repository-wide chat.
    public function studFile()
    {
        return $this->belongsTo(UserFile::class, 'stud_file_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')->orderBy('id');
    }
}
