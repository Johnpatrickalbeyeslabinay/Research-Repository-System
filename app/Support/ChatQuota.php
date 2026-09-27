<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Single source of truth for the per-user daily chat quota's cache key,
 * limit, and current usage — used by both App\Http\Middleware\
 * ChatDailyQuota (enforcement) and App\Http\Controllers\ChatController /
 * resources/views/components/chat/widget.blade.php (showing the user how
 * many questions they have left, so the limit isn't a surprise 429).
 */
final class ChatQuota
{
    /**
     * DEFAULT must match config/chat.php's 'limits.per_day'. When it didn't
     * (120 here vs 50 there) a stale config:cache silently granted 2.4x the
     * intended daily budget, billed to the real OpenAI key.
     *
     * Note the check is "not a positive integer", not config()'s own
     * missing-key default. config('x', 50) returns 50 only when the key is
     * ABSENT — a key present but null or empty returns null, and (int) null
     * is 0. That is reachable in practice: config/chat.php reads
     * (int) env('CHAT_RATE_PER_DAY', 50), and an env file containing a bare
     * `CHAT_RATE_PER_DAY=` yields '' -> 0. A limit of 0 locks every user out
     * of the chatbot entirely, so a misconfiguration must not be able to
     * produce one.
     *
     * A fallback is kept rather than removed deliberately: failing closed is
     * right for permissions (see App\Support\ResearchAccess) and wrong for a
     * spend cap, where it is a denial of service against our own users.
     */
    private const DEFAULT_PER_DAY = 50;

    public static function limit(): int
    {
        $configured = (int) config('chat.limits.per_day', self::DEFAULT_PER_DAY);

        return $configured > 0 ? $configured : self::DEFAULT_PER_DAY;
    }

    public static function key(int $userId): string
    {
        return 'chat_quota:'.$userId.':'.now()->format('Ymd');
    }

    public static function used(int $userId): int
    {
        return (int) Cache::get(self::key($userId), 0);
    }

    public static function remaining(int $userId): int
    {
        return max(0, self::limit() - self::used($userId));
    }

    /** @return array{used: int, limit: int, remaining: int} */
    public static function status(int $userId): array
    {
        $used = self::used($userId);
        $limit = self::limit();

        return ['used' => $used, 'limit' => $limit, 'remaining' => max(0, $limit - $used)];
    }
}
