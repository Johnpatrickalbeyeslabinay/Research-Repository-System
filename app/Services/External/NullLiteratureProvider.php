<?php

namespace App\Services\External;

/**
 * The default binding, and the reason this whole subsystem is safe to ship
 * before anyone decides to enable it.
 *
 * With EXTERNAL_LITERATURE_DRIVER unset, the app makes no outbound calls and
 * behaves exactly as it did before the feature existed. That makes "off" the
 * genuinely inert state rather than a flag someone has to remember to check.
 */
final class NullLiteratureProvider implements LiteratureProvider
{
    public function name(): string
    {
        return 'none';
    }

    public function search(string $query, int $limit, array $filters = []): ExternalSearchResult
    {
        return new ExternalSearchResult(provider: 'none', query: $query);
    }
}
