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

    /**
     * Every operation this app actually writes. The migration's inline
     * comment listed only four and omitted two that were already in use
     * ('query_condense', 'catalog') while naming two that were never
     * written at all ('doc_embedding', 'metadata') — those two are now
     * genuinely recorded, from App\Jobs\ProcessResearchDocument and
     * App\Services\Ai\MetadataAnalysisService respectively.
     *
     * Kept as constants so a report can enumerate them instead of
     * discovering operations by querying DISTINCT on production data.
     */
    public const OPERATIONS = [
        'chat_answer',
        'catalog',
        'query_embedding',
        'query_condense',
        'doc_embedding',
        'metadata',
        'abstract',
        'external_search',
    ];

    protected $fillable = [
        'user_id',
        'conversation_id',
        'request_id',
        'operation',
        'model',
        'prompt_tokens',
        'cached_prompt_tokens',
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
     * USD for this call, priced at report time from config/ai_pricing.php.
     *
     * An accessor rather than a column on purpose: prices change, and a
     * stored cost would leave wrong numbers in rows nobody can identify as
     * stale. Computing it means correcting a rate re-prices all history.
     */
    public function getEstimatedCostUsdAttribute(): float
    {
        return \App\Support\AiCost::usd(
            $this->model,
            $this->prompt_tokens,
            $this->completion_tokens,
            $this->cached_prompt_tokens,
        );
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
