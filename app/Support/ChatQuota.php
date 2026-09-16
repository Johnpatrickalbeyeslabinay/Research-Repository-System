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
    public static function limit(): int
    {
        return (int) config('chat.limits.per_day', 120);
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
