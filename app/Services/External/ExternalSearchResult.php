<?php

namespace App\Services\External;

/**
 * The outcome of one external lookup.
 *
 * $degraded is the field that matters operationally: it distinguishes "the
 * index genuinely holds nothing on this topic" from "we could not reach the
 * index". Those look identical in an empty array and call for completely
 * different messages to the user.
 */
final class ExternalSearchResult
{
    /**
     * @param  ExternalWork[]  $works
     */
    public function __construct(
        public readonly array $works = [],
        public readonly string $provider = 'none',
        public readonly string $query = '',
        public readonly bool $degraded = false,
        public readonly int $latencyMs = 0,
    ) {}

    public static function degraded(string $provider, string $query, int $latencyMs = 0): self
    {
        return new self([], $provider, $query, degraded: true, latencyMs: $latencyMs);
    }

    public function isEmpty(): bool
    {
        return empty($this->works);
    }
}
