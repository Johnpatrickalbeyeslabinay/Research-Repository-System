<?php

namespace App\Services\Chat;

use Illuminate\Support\Collection;

/**
 * Turns the model's [n] citation markers into structured citation payloads,
 * using the exact evidence_order mapping App\Services\Chat\PromptBuilder
 * produced for this turn (index 0 = citation [1], etc.) — never anything
 * the client supplies, which is what makes chunk-ID guessing impossible by
 * construction (see the project's RAG plan, Deliverable 27). A marker
 * outside that range is a hallucination: stripped from the answer text
 * rather than rendered as a broken citation.
 */
class CitationBuilder
{
    /**
     * @param  array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string}>  $evidenceOrder
     * @param  Collection<int, \App\Models\UserFile>  $files  keyed by stud_file.id
     * @return array{text: string, citations: array<int, array>}
     */
    public function build(string $answer, array $evidenceOrder, Collection $files): array
    {
        $maxIndex = count($evidenceOrder);

        preg_match_all('/\[(\d+)\]/', $answer, $matches);
        $usedIndexes = array_values(array_unique(array_map('intval', $matches[1] ?? [])));
        $validIndexes = array_values(array_filter($usedIndexes, fn ($i) => $i >= 1 && $i <= $maxIndex));
        sort($validIndexes);

        // Renumber consecutively in citation order — if the model cited
        // [1] and [3] but never [2], the rendered "Sources" list should
        // read 1, 2, not 1, 3.
        $renumberMap = array_flip($validIndexes); // old marker => new position (0-based)

        $text = preg_replace_callback('/\[(\d+)\]/', function ($m) use ($validIndexes, $renumberMap) {
            $old = (int) $m[1];

            if (! in_array($old, $validIndexes, true)) {
                return ''; // hallucinated marker — strip, never render
            }

            return '['.($renumberMap[$old] + 1).']';
        }, $answer);

        // Collapse whitespace left behind where a stripped marker sat
        // (e.g. "the results [9] show" -> "the results  show").
        $text = trim((string) preg_replace('/[ \t]{2,}/', ' ', (string) $text));

        $citations = [];

        foreach ($validIndexes as $old) {
            $chunk = $evidenceOrder[$old - 1];
            $file = $files[$chunk['stud_file_id']] ?? null;

            $citation = [
                'n' => $renumberMap[$old] + 1,
                'chunk_id' => $chunk['id'],
                'stud_file_id' => $chunk['stud_file_id'],
                'title' => $file?->title ?? 'Untitled',
                'authors' => $file ? $file->researcherNames() : [],
                // Same fallback meta.blade.php already uses for legacy
                // records that predate publication_year.
                'year' => $file?->publication_year ?? $file?->created_at?->format('Y'),
                'section' => $chunk['section'],
                'url' => $file ? route('research.show', $file->id) : null,
            ];

            // Never guess a page number — omit the key entirely rather
            // than render a fabricated one.
            if ($chunk['page_number'] !== null) {
                $citation['page_number'] = $chunk['page_number'];
            }

            $citations[] = $citation;
        }

        return ['text' => $text, 'citations' => $citations];
    }
}
