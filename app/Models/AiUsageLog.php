<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-call AI usage/cost telemetry. Deliberately never stores question/
 * answer text or document content — see the migration's docblock.
 */
class AiUsageLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'conversation_id',
        'request_id',
        'operation',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'latency_ms',
        'chunks_retrieved',
        'success',
        'error_code',
    ];

    protected $casts = [
        'success' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * One row, fire-and-forget. Failures here must never take down the
     * request that triggered them — usage logging is observability, not a
     * business-critical write.
     */
    public static function record(array $attributes): void
    {
        try {
            static::create($attributes);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ai_usage_logs write failed', [
                'error' => $e->getMessage(),
                'operation' => $attributes['operation'] ?? null,
            ]);
        }
    }
}
