<?php

namespace App\Services\Chat;

use App\Services\External\ExternalSearchResult;
use App\Services\External\ExternalWork;

/**
 * Turns an external lookup into an answer plus its sources.
 *
 * Two things this class is responsible for, both of them boundaries rather than
 * formatting:
 *
 * 1. THE MARKER NAMESPACE. External works are numbered [E1], [E2] — never [1].
 *    App\Services\Chat\CitationBuilder parses internal markers with
 *    /\[(\d+)\]/, which CANNOT match "[E1]", so external references are
 *    structurally invisible to internal citation parsing and an internal number
 *    can never be re-pointed at an outside paper. That is the single most
 *    important decision in this subsystem, and it costs nothing.
 *
 * 2. THE CLAIM BOUNDARY. The text produced here says what each work is ABOUT
 *    and states plainly that it is not in this repository. It never
 *    characterises findings, because only a title and abstract were fetched —
 *    the same rule the system prompt enforces for the model, applied here
 *    because on this path there IS no model: the answer is assembled in PHP,
 *    which means the boundary is guaranteed rather than instructed.
 */
class ExternalCitationBuilder
{
    /**
     * Returns [answer text, external_sources rows].
     *
     * @return array{0: string, 1: array<int, array<string, mixed>>}
     */
    public function build(ExternalSearchResult $result): array
    {
        if ($result->degraded) {
            return [
                "I couldn't reach the external research index just now, so I can only draw on "
                    ."this repository. Try again in a little while, or ask me what's available here.",
                [],
            ];
        }

        if ($result->isEmpty()) {
            return [
                'I searched public open-access research and found nothing matching that. '
                    .'The wording may be too specific — try a broader topic, or ask me what this repository holds.',
                [],
            ];
        }

        $lines = [
            'None of this is in this repository — these are published open-access studies from '
                .'outside it, so you would need to read them at the source. I can only see each '
                ."one's title and abstract, not its full text:",
            '',
        ];

        $sources = [];
        $n = 0;

        foreach ($result->works as $work) {
            $n++;
            $sources[] = $work->toCitation($n);
            $lines[] = $this->renderWork($work, $n);
        }

        $lines[] = '';
        $lines[] = 'For anything in this repository itself, ask me directly and I will cite the studies on file.';

        return [implode("\n", $lines), $sources];
    }

    private function renderWork(ExternalWork $work, int $n): string
    {
        $bits = [];

        if (! empty($work->authors)) {
            $bits[] = $work->authors[0].(count($work->authors) > 1 ? ' et al.' : '');
        }

        if ($work->year !== null) {
            $bits[] = (string) $work->year;
        }

        if ($work->venue !== null) {
            $bits[] = $work->venue;
        }

        $line = "- [E{$n}] **{$work->title}**";

        if (! empty($bits)) {
            $line .= ' — '.implode(', ', $bits);
        }

        // Stated as what the study is ABOUT, never as what it found. An
        // abstract is all we have, and presenting it as established findings
        // would be exactly the overreach rule 15 forbids the model.
        if ($work->abstract !== null) {
            $line .= "\n  What it covers: ".$work->abstract;
        }

        return $line;
    }
}
