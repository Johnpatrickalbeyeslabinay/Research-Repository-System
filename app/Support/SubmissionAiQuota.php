<?php

namespace App\Support;

use App\Exceptions\AiQuotaExceededException;
use Illuminate\Support\Facades\Cache;

/**
 * Per-user daily cap on the submission form's AI calls (abstract
 * generation and keyword/SDG suggestions), the counterpart of ChatQuota
 * for the chatbot. Without it the only guard was the route's
 * throttle:10,1, which bounds burst rate but not a student looping
 * "Analyze" all day with slightly edited text.
 *
 * consume() is called by the AI services themselves, and only on a cache
 * miss right before the OpenAI request — so reloading the page, re-picking
 * the same PDF or re-clicking on unchanged text never counts.
 */
final class SubmissionAiQuota
{
    /**
     * Same reasoning as ChatQuota::DEFAULT_PER_DAY: a present-but-empty env
     * value casts to 0, and a limit of 0 would lock every student out of
     * the feature, so anything not positive falls back to this.
     */
    private const DEFAULT_PER_DAY = 10;

    public static function limit(): int
    {
        $configured = (int) config('research.ai.per_day', self::DEFAULT_PER_DAY);

        return $configured > 0 ? $configured : self::DEFAULT_PER_DAY;
    }

    public static function key(int $userId): string
    {
        return 'submission_ai_quota:'.$userId.':'.now()->format('Ymd');
    }

    public static function used(int $userId): int
    {
        return (int) Cache::get(self::key($userId), 0);
    }

    /** @return array{used: int, limit: int, remaining: int} */
    public static function status(int $userId): array
    {
        $used = self::used($userId);
        $limit = self::limit();

        return ['used' => $used, 'limit' => $limit, 'remaining' => max(0, $limit - $used)];
    }

    /**
     * Counts one AI call against today's quota, or throws if none is left.
     * The check and the increment run under one lock (same pattern as
     * ChatDailyQuota::recordUsage) so two concurrent clicks can't both
     * slip through on the last remaining use.
     *
     * A null user (queue/console) is not metered — there is no student
     * to bill it to.
     *
     * @throws AiQuotaExceededException
     */
    public static function consume(?int $userId): void
    {
        if ($userId === null) {
            return;
        }

        $key = self::key($userId);
        $limit = self::limit();

        Cache::lock($key.':lock', 5)->block(2, function () use ($key, $limit) {
            if ((int) Cache::get($key, 0) >= $limit) {
                throw AiQuotaExceededException::forLimit($limit);
            }

            Cache::add($key, 0, now()->endOfDay());
            Cache::increment($key);
        });
    }
}
