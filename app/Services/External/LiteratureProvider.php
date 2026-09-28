<?php

namespace App\Services\External;

/**
 * An outside index of published research.
 *
 * The one hard rule: search() NEVER throws. A secondary, optional source must
 * not be able to turn a user's question into a 500 — a provider outage has to
 * degrade to the ordinary no-evidence answer. Implementations catch
 * \Throwable themselves and return a degraded ExternalSearchResult.
 */
interface LiteratureProvider
{
    public function name(): string;

    /**
     * @param  array{year_from?: ?int, year_to?: ?int}  $filters
     */
    public function search(string $query, int $limit, array $filters = []): ExternalSearchResult;
}
