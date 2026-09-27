<?php

namespace App\Services\External;

use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * The single entry point for "is there published research about this outside
 * our repository?".
 *
 * NO TABLE, NO EMBEDDINGS, NO HARVEST — and that is a deliberate rejection of
 * the obvious alternative, for three reasons:
 *
 *  1. Vercel's Hobby plan allows ONE cron invocation per day, and that slot is
 *     already spent on /internal/queue/drain — the mechanism that exists
 *     because indexing once failed silently and left the entire corpus
 *     unembedded (see routes/web.php). A harvest would compete with the one job
 *     that must not be starved.
 *  2. Embedding a harvest costs money forever, proportional to its size, for a
 *     source explicitly scoped as cheap and secondary. OpenAlex's own relevance
 *     ranking over 250M works beats anything we would get from embedding a few
 *     thousand locally-cached abstracts.
 *  3. A table of external works sitting next to research_chunks is an
 *     invitation to join them — and ResearchAccess, this app's entire
 *     permission model, has no meaning for a row nobody owns. The safest
 *     version of that mistake is the one that cannot be made.
 *
 * So results are cached at the RESPONSE level in the ordinary cache store
 * (Postgres-backed, therefore serverless-safe), keyed by the normalized query.
 * Repeat questions are free; nothing accumulates that has to be secured.
 */
class ExternalLiteratureService
{
    public function __construct(
        private LiteratureProvider $provider,
        private ProviderCircuit $circuit,
    ) {}

    public function isAvailable(): bool
    {
        return (bool) config('external_literature.enabled')
            && ! $this->provider instanceof NullLiteratureProvider
            && ! $this->circuit->isOpen($this->provider->name());
    }

    /**
     * @param  array{year_from?: ?int, year_to?: ?int}  $filters
     */
    public function search(string $topic, array $filters = [], ?int $userId = null): ExternalSearchResult
    {
        if (! $this->isAvailable()) {
            return new ExternalSearchResult(provider: $this->provider->name(), query: $topic);
        }

        $limit = (int) config('external_literature.max_works', 5);
        $key = $this->cacheKey($topic, $filters, $limit);

        // A cache hit is not logged and does not count against the daily cap:
        // no outbound request was made, so reporting one would overstate both
        // our usage of their service and the user's.
        if ($cached = Cache::get($key)) {
            return $this->hydrate($cached, $topic);
        }

        if ($userId !== null && ! $this->withinDailyCap($userId)) {
            return new ExternalSearchResult(provider: $this->provider->name(), query: $topic);
        }

        $result = $this->provider->search($topic, $limit, $filters);

        if ($result->degraded) {
            $this->circuit->recordFailure($this->provider->name());
        } else {
            $this->circuit->recordSuccess($this->provider->name());

            // Empty results are cached too. "OpenAlex has nothing on this" is a
            // real, stable answer, and re-asking them the same dead-end
            // question every time a student retries it is the rudest possible
            // use of a free service.
            Cache::put(
                $key,
                array_map(fn (ExternalWork $w) => (array) $w, $result->works),
                now()->addDays((int) config('external_literature.cache_ttl_days', 30)),
            );
        }

        if ($userId !== null) {
            $this->countAgainstDailyCap($userId);
        }

        $this->recordUsage($result, $userId);

        return $result;
    }

    /**
     * Telemetry for a subsystem that costs no tokens.
     *
     * Logged anyway, with model 'openalex' and no token counts, because
     * latency, success rate and result counts are exactly what you need when
     * a fail-soft dependency starts failing — and a path that writes nothing
     * to ai_usage_logs is a path nobody notices breaking. App\Support\AiCost
     * prices 'openalex' at zero by design.
     */
    private function recordUsage(ExternalSearchResult $result, ?int $userId): void
    {
        AiUsageLog::record([
            'user_id' => $userId,
            'request_id' => (string) Str::uuid(),
            'operation' => 'external_search',
            'model' => $result->provider,
            'latency_ms' => $result->latencyMs,
            'chunks_retrieved' => count($result->works),
            'success' => ! $result->degraded,
            'error_code' => $result->degraded ? 'provider_unavailable' : null,
        ]);
    }

    private function withinDailyCap(int $userId): bool
    {
        $cap = (int) config('external_literature.per_user_per_day', 20);

        return (int) Cache::get($this->capKey($userId), 0) < $cap;
    }

    private function countAgainstDailyCap(int $userId): void
    {
        $key = $this->capKey($userId);

        Cache::put($key, (int) Cache::get($key, 0) + 1, now()->endOfDay());
    }

    private function capKey(int $userId): string
    {
        return 'extlit:cap:'.$userId.':'.now()->format('Ymd');
    }

    /**
     * Keyed on the NORMALIZED query so trivial rephrasings share a cache entry,
     * and on the provider + limit so changing either does not serve stale
     * shapes. Deliberately not keyed by user: an external search carries no
     * permission dimension — there is nothing private about what OpenAlex
     * publishes — which is why this cache is safe to share and the internal
     * answer cache is not.
     */
    private function cacheKey(string $topic, array $filters, int $limit): string
    {
        $normalized = mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $topic)));

        return 'extlit:v1:'.$this->provider->name().':'.hash('sha256', implode('|', [
            $normalized,
            $limit,
            $filters['year_from'] ?? '',
            $filters['year_to'] ?? '',
        ]));
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function hydrate(array $rows, string $topic): ExternalSearchResult
    {
        $works = array_map(fn (array $row) => new ExternalWork(
            provider: (string) ($row['provider'] ?? $this->provider->name()),
            externalId: (string) ($row['externalId'] ?? ''),
            title: (string) ($row['title'] ?? ''),
            abstract: $row['abstract'] ?? null,
            year: $row['year'] ?? null,
            authors: $row['authors'] ?? [],
            doi: $row['doi'] ?? null,
            url: $row['url'] ?? null,
            venue: $row['venue'] ?? null,
            isOpenAccess: (bool) ($row['isOpenAccess'] ?? false),
            citedByCount: $row['citedByCount'] ?? null,
        ), $rows);

        return new ExternalSearchResult(
            works: $works,
            provider: $this->provider->name(),
            query: $topic,
        );
    }
}
