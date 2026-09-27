<?php

namespace App\Services\Research;

use App\Services\Pdf\SectionDetector;
use App\Support\TokenEstimator;

/**
 * Splits an already-extracted research document into section-aware,
 * overlapping chunks ready for embedding. Purely deterministic text
 * processing — no AI involved. References/Bibliography is excluded
 * entirely, and repeated headers/footers + standalone page-number lines
 * are stripped before chunking.
 */
class ChunkingService
{
    // SectionDetector::pageStartOffsets()/pageNumberForOffset() are reused
    // here (not duplicated) to resolve the page each individual chunk
    // actually starts on. Previously every chunk in a section inherited
    // that section's *heading* page number — a chunk 40 pages into Results
    // would cite the page "RESULTS" appeared on, not its own page. That's a
    // fabricated citation.
    public function __construct(
        private SectionDetector $sectionDetector,
        private TokenEstimator $tokens,
    ) {}

    /**
     * @param  string[]  $pages  per-page extracted text (from PdfTextExtractor)
     * @param  string  $fullText  the pages joined with "\n" (same as SectionDetector's input)
     * @param  array  $sections  SectionDetector::detect()['sections'] output
     * @param  ?string  $documentSummary  when provided, prepended as a single
     *                                    chunk_index=0, section='metadata' chunk
     *                                    (title/authors/year/keywords/SDGs/abstract)
     *                                    — see App\Jobs\ProcessResearchDocument for
     *                                    how it's built. This is what makes broad
     *                                    discovery questions ("what research exists
     *                                    about X?") match well; body prose alone
     *                                    matches those poorly.
     * @return array{
     *     chunks: array<int, array{chunk_index: int, section: ?string, page_number: ?int, content: string, token_count: int, content_hash: string}>,
     *     truncated: bool,
     * }
     */
    public function chunk(
        array $pages,
        string $fullText,
        array $sections,
        ?string $documentSummary = null,
        ?string $abstractFallback = null,
    ): array {
        $noiseLines = $this->detectRepeatedLines($pages);
        $ranges = $this->chunkableRanges($sections, $fullText);
        $pageStarts = $this->sectionDetector->pageStartOffsets($pages);

        $targetWords = (int) config('research.chunking.target_words', 375);
        $overlapWords = (int) config('research.chunking.overlap_words', 56);
        $step = max(1, $targetWords - $overlapWords);
        // Ceiling on chunks per document — nothing else bounds this, and an
        // unusually long thesis (e.g. 300 pages) would otherwise produce
        // 1000+ chunks and embedding calls for a single submission.
        $maxChunks = (int) config('research.chunking.max_chunks', 400);

        $chunks = [];
        $index = 0;
        $truncated = false;

        $documentSummary = $documentSummary !== null ? trim($documentSummary) : null;

        if ($documentSummary !== null && $documentSummary !== '') {
            $chunks[] = [
                'chunk_index' => $index++,
                'section' => 'metadata',
                'page_number' => null,
                'content' => $documentSummary,
                'token_count' => $this->tokens->estimate($documentSummary),
                'content_hash' => hash('sha256', $documentSummary),
            ];
        }

        // A real abstract chunk for documents where SectionDetector found no
        // ABSTRACT heading — a scanned PDF, an unusual layout, or an approval
        // sheet before the abstract. Without this those documents have no
        // section='abstract' chunk at all, so the Abstract retrieval tier
        // (App\Services\Retrieval\Tier) can only see the synthetic metadata
        // chunk and EvidenceSufficiencyGate escalates every one of their
        // questions to full text — the exact documents where the cheap tier
        // would have paid off most get none of its benefit.
        //
        // The text comes from the student's typed abstract or the one parsed
        // out of the PDF, so it is already stored; this only makes it
        // retrievable at the right granularity. Costs at most one extra chunk
        // per document, and content-hash reuse means re-indexing an unchanged
        // document re-embeds nothing.
        $abstractFallback = $abstractFallback !== null ? trim($abstractFallback) : null;

        if ($abstractFallback !== null && $abstractFallback !== '' && ! $this->hasAbstractSection($sections)) {
            $chunks[] = [
                'chunk_index' => $index++,
                'section' => 'abstract',
                // Deliberately null: this text was assembled from stored
                // metadata, not located on a page. A page number here would be
                // a fabricated citation, which is what the per-chunk page
                // resolution in this class exists to prevent.
                'page_number' => null,
                'content' => $abstractFallback,
                'token_count' => $this->tokens->estimate($abstractFallback),
                'content_hash' => hash('sha256', $abstractFallback),
            ];
        }

        foreach ($ranges as $range) {
            if (count($chunks) >= $maxChunks) {
                $truncated = true;
                break;
            }

            $sectionText = substr($fullText, $range['start'], $range['end'] - $range['start']);
            $wordOffsets = $this->cleanWordsWithOffsets($sectionText, $range['start'], $noiseLines);
            $totalWords = count($wordOffsets);

            if ($totalWords === 0) {
                continue;
            }

            for ($offset = 0; $offset < $totalWords; $offset += $step) {
                if (count($chunks) >= $maxChunks) {
                    $truncated = true;
                    break 2;
                }

                $slice = array_slice($wordOffsets, $offset, $targetWords);

                if (empty($slice)) {
                    break;
                }

                $content = implode(' ', array_column($slice, 0));

                $chunks[] = [
                    'chunk_index' => $index++,
                    'section' => $range['key'],
                    // Resolved from this chunk's own starting offset, not the
                    // section's heading offset — see the class docblock note
                    // above. A page that can't be resolved is left null;
                    // a wrong page would be a fabricated citation.
                    'page_number' => $this->sectionDetector->pageNumberForOffset($slice[0][1], $pageStarts),
                    'content' => $content,
                    // App\Support\TokenEstimator rather than a local formula:
                    // the word-count estimate this replaced scored zero for
                    // digits and symbols, so numeric results text was priced
                    // at roughly a quarter of what it actually costs.
                    'token_count' => $this->tokens->estimate($content),
                    'content_hash' => hash('sha256', $content),
                ];

                if ($offset + $targetWords >= $totalWords) {
                    break;
                }
            }
        }

        return ['chunks' => $chunks, 'truncated' => $truncated];
    }

    /**
     * @param  array<int, array{key: string, start: int, end: int, page_number: ?int}>  $sections
     * @return array<int, array{key: ?string, page_number: ?int, start: int, end: int}>
     */
    /**
     * Whether SectionDetector located an ABSTRACT heading in the document.
     *
     * @param  array<int, array{key: ?string}>  $sections
     */
    private function hasAbstractSection(array $sections): bool
    {
        foreach ($sections as $section) {
            if (($section['key'] ?? null) === 'abstract') {
                return true;
            }
        }

        return false;
    }

    private function chunkableRanges(array $sections, string $fullText): array
    {
        if (empty($sections)) {
            return [[
                'key' => null,
                'page_number' => null,
                'start' => 0,
                'end' => strlen($fullText),
            ]];
        }

        $ranges = [];

        foreach ($sections as $section) {
            if ($section['key'] === 'references') {
                continue;
            }

            $ranges[] = [
                'key' => $section['key'],
                'page_number' => $section['page_number'],
                'start' => $section['start'],
                'end' => $section['end'],
            ];
        }

        return $ranges;
    }

    /**
     * Lines that repeat near-identically across more than half of the pages
     * are treated as running headers/footers and stripped. Only meaningful
     * with enough pages to establish a pattern.
     *
     * @param  string[]  $pages
     * @return array<string, true> lowercase-normalized line => true
     */
    private function detectRepeatedLines(array $pages): array
    {
        if (count($pages) < 3) {
            return [];
        }

        $counts = [];

        foreach ($pages as $pageText) {
            $seenOnThisPage = [];

            foreach (explode("\n", $pageText) as $line) {
                $trimmed = trim($line);

                if ($trimmed === '' || mb_strlen($trimmed) > 100) {
                    continue;
                }

                $key = mb_strtolower($trimmed);

                if (isset($seenOnThisPage[$key])) {
                    continue; // count a repeated line once per page
                }

                $seenOnThisPage[$key] = true;
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        $threshold = count($pages) / 2;
        $noise = [];

        foreach ($counts as $key => $count) {
            if ($count > $threshold) {
                $noise[$key] = true;
            }
        }

        return $noise;
    }

    /**
     * Same filtering cleanText() used to do (blank lines, standalone page
     * numbers, repeated header/footer lines) but returns each surviving
     * word paired with the absolute $fullText offset of the *line* it came
     * from, instead of collapsing straight to a string. A line never spans
     * a page boundary, so a line-level offset is exact enough to resolve
     * which page a chunk starts on (see chunk() above) while staying just
     * as simple as the string-collapsing version this replaced.
     *
     * @param  array<string, true>  $noiseLines
     * @return array<int, array{0: string, 1: int}> [word, absoluteOffset]
     */
    private function cleanWordsWithOffsets(string $sectionText, int $sectionStart, array $noiseLines): array
    {
        $words = [];
        $cursor = $sectionStart;

        foreach (explode("\n", $sectionText) as $line) {
            $trimmed = trim($line);
            $lineOffset = $cursor;
            $cursor += strlen($line) + 1; // +1 for the "\n" explode() consumed

            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^\d{1,4}$/', $trimmed)) {
                continue; // standalone page number
            }

            if (isset($noiseLines[mb_strtolower($trimmed)])) {
                continue;
            }

            foreach (preg_split('/\s+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY) as $word) {
                $words[] = [$word, $lineOffset];
            }
        }

        return $words;
    }
}
