<?php

namespace App\Services\External;

/**
 * One publication from an outside open-access index.
 *
 * Metadata only, and the type enforces that: there is no field for full text,
 * because none is ever fetched. What the chatbot may say about one of these is
 * bounded by what is here — a title, who wrote it, when, and an abstract.
 */
final class ExternalWork
{
    public function __construct(
        public readonly string $provider,
        public readonly string $externalId,
        public readonly string $title,
        /** Reconstructed and truncated; null is common and must be tolerated. */
        public readonly ?string $abstract = null,
        public readonly ?int $year = null,
        /** @var string[] */
        public readonly array $authors = [],
        /** Bare DOI, lowercased, no resolver prefix: "10.1234/abcd". */
        public readonly ?string $doi = null,
        public readonly ?string $url = null,
        public readonly ?string $venue = null,
        public readonly bool $isOpenAccess = false,
        public readonly ?int $citedByCount = null,
    ) {}

    /** A resolvable link, preferring the DOI over the provider's own page. */
    public function link(): ?string
    {
        if ($this->doi !== null && $this->doi !== '') {
            return 'https://doi.org/'.$this->doi;
        }

        return $this->url;
    }

    /**
     * The shape persisted to chat_messages.external_sources and returned to the
     * widget. Kept deliberately separate from an internal citation's shape —
     * see App\Services\Chat\ExternalCitationBuilder.
     */
    public function toCitation(int $n): array
    {
        return [
            // "E1", never 1. The E prefix is the whole separation mechanism.
            'n' => 'E'.$n,
            'source' => 'external',
            'provider' => $this->provider,
            'title' => $this->title,
            'authors' => $this->authors,
            'year' => $this->year,
            'venue' => $this->venue,
            'doi' => $this->doi,
            'url' => $this->link(),
        ];
    }
}
