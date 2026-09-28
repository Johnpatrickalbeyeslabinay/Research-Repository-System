<?php

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAlex — the one external index worth integrating first.
 *
 * No API key, CC0 licensing, a documented politeness convention, and it already
 * aggregates Crossref, PubMed, DOAJ and MAG — so one integration covers the
 * ground three separate ones would, which is why this ships alone behind the
 * LiteratureProvider interface rather than alongside Crossref and DOAJ drivers
 * nobody has needed yet.
 *
 * Two things about their API shape this class exists to absorb:
 *
 *  - Abstracts come as an INVERTED INDEX ({"Fish": [0], "grow": [1, 4]}), not
 *    prose, so they have to be reassembled by position.
 *  - That field is frequently null, and the surrounding schema churns
 *    (`concepts` became `topics`). So every field here is read defensively and
 *    a missing abstract yields a title-only work rather than a discarded one —
 *    a title and a DOI is still a useful pointer.
 *
 * Raw Http:: rather than an SDK, matching every other API client in this
 * project (ChatAnswerService, EmbeddingService, MetadataAnalysisService). That
 * is what keeps Http::fake() the test seam.
 */
final class OpenAlexProvider implements LiteratureProvider
{
    private const ENDPOINT = 'https://api.openalex.org/works';

    public function name(): string
    {
        return 'openalex';
    }

    public function search(string $query, int $limit, array $filters = []): ExternalSearchResult
    {
        $query = trim($query);

        if ($query === '' || $limit < 1) {
            return new ExternalSearchResult(provider: $this->name(), query: $query);
        }

        $start = microtime(true);

        try {
            $response = Http::withHeaders(['User-Agent' => $this->userAgent()])
                ->connectTimeout((int) config('external_literature.connect_timeout', 3))
                ->timeout((int) config('external_literature.timeout', 6))
                ->get(self::ENDPOINT, $this->queryParameters($query, $limit, $filters));
        } catch (\Throwable $e) {
            // Never rethrow. A secondary source must not be able to turn a
            // user's question into a 500.
            Log::warning('OpenAlex request failed', ['error' => $e->getMessage()]);

            return ExternalSearchResult::degraded($this->name(), $query, $this->elapsed($start));
        }

        $latencyMs = $this->elapsed($start);

        if ($response->failed()) {
            Log::warning('OpenAlex returned an error', [
                'status' => $response->status(),
            ]);

            return ExternalSearchResult::degraded($this->name(), $query, $latencyMs);
        }

        $results = $response->json('results');

        if (! is_array($results)) {
            // A 200 with an unexpected body is a schema change, not an outage —
            // but it is still degraded, because we cannot tell "no matches"
            // from "we no longer understand the response".
            Log::warning('OpenAlex response had no results array');

            return ExternalSearchResult::degraded($this->name(), $query, $latencyMs);
        }

        $works = [];

        foreach ($results as $raw) {
            if (! is_array($raw)) {
                continue;
            }

            $work = $this->toWork($raw);

            if ($work !== null) {
                $works[] = $work;
            }
        }

        return new ExternalSearchResult(
            works: $works,
            provider: $this->name(),
            query: $query,
            latencyMs: $latencyMs,
        );
    }

    private function queryParameters(string $query, int $limit, array $filters): array
    {
        $params = [
            'search' => $query,
            'per_page' => $limit,
            // Only genuinely open-access work: pointing a student at a paywall
            // they cannot read is worse than not mentioning it.
            'filter' => 'is_oa:true',
            'select' => 'id,doi,title,abstract_inverted_index,publication_year,authorships,primary_location,open_access,cited_by_count',
        ];

        $from = $filters['year_from'] ?? null;
        $to = $filters['year_to'] ?? null;

        if ($from !== null || $to !== null) {
            $params['filter'] .= ',from_publication_date:'.($from ?? 1800).'-01-01'
                .',to_publication_date:'.($to ?? (int) date('Y')).'-12-31';
        }

        if ($mailto = config('external_literature.mailto')) {
            $params['mailto'] = $mailto;
        }

        return $params;
    }

    private function toWork(array $raw): ?ExternalWork
    {
        $title = $this->cleanString($raw['title'] ?? null);

        // A work with no title is unusable as a citation — there would be
        // nothing to show the user.
        if ($title === null) {
            return null;
        }

        return new ExternalWork(
            provider: $this->name(),
            externalId: $this->shortId($raw['id'] ?? null),
            title: $title,
            abstract: $this->reconstructAbstract($raw['abstract_inverted_index'] ?? null),
            year: isset($raw['publication_year']) ? (int) $raw['publication_year'] : null,
            authors: $this->authors($raw['authorships'] ?? []),
            doi: $this->normalizeDoi($raw['doi'] ?? null),
            url: $this->cleanString($raw['id'] ?? null),
            venue: $this->cleanString($raw['primary_location']['source']['display_name'] ?? null),
            isOpenAccess: (bool) ($raw['open_access']['is_oa'] ?? false),
            citedByCount: isset($raw['cited_by_count']) ? (int) $raw['cited_by_count'] : null,
        );
    }

    /**
     * Rebuilds prose from OpenAlex's inverted index.
     *
     * The field maps each word to every position it occupies
     * ({"Fish": [0], "grow": [1, 4]}), so reassembly means scattering words
     * into a positional array and joining it. Gaps are possible when the source
     * data is incomplete, so the array is sorted by key rather than assumed
     * contiguous.
     *
     * Returns null for a missing or unusable index — common enough that callers
     * must treat a title-only work as normal.
     */
    private function reconstructAbstract(mixed $inverted): ?string
    {
        if (! is_array($inverted) || empty($inverted)) {
            return null;
        }

        $positions = [];

        foreach ($inverted as $word => $indices) {
            if (! is_array($indices)) {
                continue;
            }

            foreach ($indices as $index) {
                if (is_int($index) || ctype_digit((string) $index)) {
                    $positions[(int) $index] = (string) $word;
                }
            }
        }

        if (empty($positions)) {
            return null;
        }

        ksort($positions);

        $text = $this->cleanString(implode(' ', $positions));

        if ($text === null) {
            return null;
        }

        $max = (int) config('external_literature.abstract_chars', 240);

        return mb_strlen($text) > $max
            ? rtrim(mb_substr($text, 0, $max)).'…'
            : $text;
    }

    /**
     * @return string[]
     */
    private function authors(mixed $authorships): array
    {
        if (! is_array($authorships)) {
            return [];
        }

        $names = [];

        foreach ($authorships as $authorship) {
            $name = $this->cleanString($authorship['author']['display_name'] ?? null);

            if ($name !== null) {
                $names[] = $name;
            }

            // Enough to identify the paper; a 40-author physics collaboration
            // would otherwise dominate the citation card.
            if (count($names) >= 5) {
                break;
            }
        }

        return $names;
    }

    /** "https://doi.org/10.1234/ABcd" -> "10.1234/abcd". */
    private function normalizeDoi(mixed $doi): ?string
    {
        $doi = $this->cleanString(is_string($doi) ? $doi : null);

        if ($doi === null) {
            return null;
        }

        $doi = preg_replace('#^https?://(dx\.)?doi\.org/#i', '', $doi);
        $doi = mb_strtolower(trim((string) $doi));

        return str_starts_with($doi, '10.') ? $doi : null;
    }

    /** "https://openalex.org/W2741809807" -> "W2741809807". */
    private function shortId(mixed $id): string
    {
        $id = $this->cleanString(is_string($id) ? $id : null) ?? '';

        return $id === '' ? '' : (string) preg_replace('#^.*/#', '', $id);
    }

    private function cleanString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        // Collapse whitespace and strip control characters: this text goes into
        // a prompt block and a DOM node, and neither wants stray newlines.
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));

        return $value === '' ? null : $value;
    }

    /**
     * OpenAlex asks callers to identify themselves. Doing so puts us in their
     * faster pool and gives them someone to contact instead of silently
     * blocking us.
     */
    private function userAgent(): string
    {
        $agent = 'FisheriesResearchRepository/1.0 (+'.(string) config('app.url').')';

        if ($mailto = config('external_literature.mailto')) {
            $agent .= ' mailto:'.$mailto;
        }

        return $agent;
    }

    private function elapsed(float $start): int
    {
        return (int) round((microtime(true) - $start) * 1000);
    }
}
