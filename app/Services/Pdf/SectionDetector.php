<?php

namespace App\Services\Pdf;

use Illuminate\Support\Facades\Cache;

/**
 * Deterministic, regex/heuristic-based section detection over PDF-extracted
 * text — no generative AI. Tolerant of the common heading wording/case
 * variations found in student research papers.
 */
class SectionDetector
{
    /**
     * Canonical section key => the exact heading phrases (already
     * uppercased) that identify it. Order within each list doesn't matter;
     * detection is by exact (normalized) line match, not substring, so
     * overlapping phrases like METHODOLOGY/METHODS never conflict.
     */
    private const ALIASES = [
        'abstract' => ['ABSTRACT'],
        'introduction' => ['INTRODUCTION', 'BACKGROUND OF THE STUDY', 'BACKGROUND'],
        'methodology' => ['RESEARCH METHODOLOGY', 'METHODOLOGY', 'METHODS'],
        'results' => ['RESULTS AND DISCUSSION', 'RESULTS', 'FINDINGS'],
        'discussion' => ['DISCUSSION'],
        'conclusion' => ['CONCLUSION', 'CONCLUSIONS', 'RECOMMENDATIONS'],
        'references' => ['REFERENCES', 'BIBLIOGRAPHY'],
    ];

    private const MAX_HEADING_LENGTH = 60;

    private const ABSTRACT_MIN_WORDS = 50;

    private const ABSTRACT_MAX_WORDS = 500;

    // Bumped whenever detect()'s output changes for the same input — both
    // when PdfTextExtractor's output changes upstream (e.g. the UTF-8
    // sanitization) and when detect() itself changes (e.g. the citation-
    // header stripping added below) — so a stale bad result cached under
    // the same hash from before the fix isn't kept serving it.
    // v6: multi-line/bulleted "Keywords:" parsing + junk-token filtering,
    // and the new fallback_excerpt output.
    // v7: table-of-contents headings dropped (dropTableOfContents()), and
    // fallback_excerpt cleaned of headers/footers/citations (cleanForPrompt()).
    private const CACHE_VERSION = 'v7';

    // A parsed keyword token this long is almost certainly a mis-split
    // sentence fragment (e.g. a "Keywords:" block that runs into the next
    // paragraph), not an actual keyword — discard rather than add it as one.
    private const MAX_KEYWORD_LENGTH = 60;

    // How many words of the introduction section to use as the AI's
    // fallback excerpt when the abstract itself is missing or too short
    // (see MetadataAnalysisService's $fallbackExcerpt parameter).
    private const FALLBACK_EXCERPT_WORDS = 300;

    // Sections (in reading order) that abstractSourceExcerpt() samples for
    // AI abstract generation, and how many words of each — weighted toward
    // what an abstract actually summarizes (purpose, findings, conclusion).
    // About 750 words (~1,000 tokens) in total, so a draft costs a fraction
    // of a cent and never carries the bulk of the thesis.
    private const ABSTRACT_SOURCE_SECTIONS = [
        'introduction' => ['Introduction', 200],
        'methodology' => ['Methodology', 150],
        'results' => ['Results', 200],
        'discussion' => ['Discussion', 150],
        'conclusion' => ['Conclusion', 200],
    ];

    // Used only when no section heading was found at all.
    private const ABSTRACT_SOURCE_FALLBACK_WORDS = 600;

    // A heading followed by fewer real words than this before the next
    // heading is a candidate table-of-contents entry (see
    // dropTableOfContents()).
    private const TOC_ENTRY_MAX_WORDS = 15;

    // Front-matter headings the no-heading fallback skips past, so the AI
    // isn't sent the approval sheet, dedication or acknowledgments.
    private const FRONT_MATTER_HEADINGS = [
        'ACKNOWLEDGMENT', 'ACKNOWLEDGMENTS', 'ACKNOWLEDGEMENT', 'ACKNOWLEDGEMENTS',
        'DEDICATION', 'TABLE OF CONTENTS', 'CONTENTS', 'LIST OF TABLES',
        'LIST OF FIGURES', 'LIST OF APPENDICES', 'APPROVAL SHEET',
    ];

    /**
     * Same as detect(), but cached by the source PDF's content hash — see
     * PdfTextExtractor::extractCached() for why this matters (the same
     * document's text is otherwise re-detected up to three times per
     * submission for no reason, since detection is a pure function of hash).
     *
     * @param  string[]  $pages
     * @return array{
     *     sections: array<int, array{key: string, heading: string, start: int, end: int, page_number: ?int}>,
     *     abstract: array{text: ?string, confidence: ?string, word_count: int},
     *     keywords: string[],
     *     fallback_excerpt: ?string,
     * }
     */
    public function detectCached(string $hash, array $pages, string $fullText): array
    {
        return Cache::remember(
            self::CACHE_VERSION.":pdf_sections:{$hash}",
            now()->addMinutes(30),
            fn () => $this->detect($pages, $fullText)
        );
    }

    /**
     * @param  string[]  $pages  Per-page extracted text, joined with "\n" to form $fullText.
     * @return array{
     *     sections: array<int, array{key: string, heading: string, start: int, end: int, page_number: ?int}>,
     *     abstract: array{text: ?string, confidence: ?string, word_count: int},
     *     keywords: string[],
     *     fallback_excerpt: ?string,
     * }
     */
    public function detect(array $pages, string $fullText): array
    {
        $aliasLookup = [];
        foreach (self::ALIASES as $key => $phrases) {
            foreach ($phrases as $phrase) {
                $aliasLookup[$phrase] = $key;
            }
        }

        $lines = preg_split('/\n/', $fullText, -1, PREG_SPLIT_OFFSET_CAPTURE);
        $pageStarts = $this->pageStartOffsets($pages);

        $headings = [];

        foreach ($lines as [$line, $offset]) {
            $normalized = $this->normalizeHeadingCandidate($line);

            if ($normalized === null) {
                continue;
            }

            if (! isset($aliasLookup[$normalized])) {
                continue;
            }

            $headings[] = [
                'key' => $aliasLookup[$normalized],
                'heading' => trim($line),
                'start' => $offset,
                'page_number' => $this->pageNumberForOffset($offset, $pageStarts),
            ];
        }

        $headings = $this->dropTableOfContents($headings, $fullText);

        $sections = [];
        foreach ($headings as $i => $heading) {
            $end = $headings[$i + 1]['start'] ?? strlen($fullText);

            $sections[] = [
                'key' => $heading['key'],
                'heading' => $heading['heading'],
                'start' => $heading['start'],
                'end' => $end,
                'page_number' => $heading['page_number'],
            ];
        }

        $abstract = $this->extractAbstract($sections, $fullText);
        $keywords = $abstract['keywords'];
        unset($abstract['keywords']);

        return [
            'sections' => $sections,
            'abstract' => $abstract,
            'keywords' => $keywords,
            'references' => $this->extractReferences($sections, $fullText),
            'fallback_excerpt' => $this->extractFallbackExcerpt($sections, $fullText, $pages),
        ];
    }

    /**
     * A thesis's table of contents lists the same headings this class
     * looks for ("INTRODUCTION", "METHODOLOGY", ...), and smalot/pdfparser
     * often splits the dot leaders off so each entry sits on its own line
     * and matches exactly. Left in, every section would start at its TOC
     * entry: the introduction excerpt sent to the AI would be the rest of
     * the TOC, and extractReferences() would read the TOC's "REFERENCES"
     * entry instead of the real list.
     *
     * A TOC shows up as a run of headings with almost nothing between
     * them (a few words, or lines that end in page numbers) covering
     * several different section kinds. A run spanning at least three
     * different keys is dropped — including its last entry, whose "body"
     * runs on into whatever follows the TOC. A real "CHAPTER I /
     * INTRODUCTION" directly followed by "Background of the Study" is one
     * key, and even a terse "CONCLUSIONS / RECOMMENDATIONS / REFERENCES"
     * ending is only two, so neither is mistaken for a TOC.
     *
     * @param  array<int, array{key: string, heading: string, start: int, page_number: ?int}>  $headings
     * @return array<int, array{key: string, heading: string, start: int, page_number: ?int}>
     */
    private function dropTableOfContents(array $headings, string $fullText): array
    {
        $count = count($headings);
        $drop = [];
        $i = 0;

        while ($i < $count - 1) {
            $j = $i;

            while ($j < $count - 1 && $this->looksLikeTocGap($fullText, $headings[$j]['start'], $headings[$j + 1]['start'])) {
                $j++;
            }

            // $i..$j is a run where each heading is followed almost
            // immediately by the next one. When the TOC's last entry sits
            // just before the real first chapter, the run continues into
            // the document itself — a key showing up again after other
            // keys ("INTRODUCTION" after "REFERENCES") marks where the
            // listing ended and the real section begins.
            $tocEnd = $j;
            $seen = [$headings[$i]['key'] => true];

            for ($k = $i + 1; $k <= $j; $k++) {
                $key = $headings[$k]['key'];

                if ($key !== $headings[$k - 1]['key'] && isset($seen[$key])) {
                    $tocEnd = $k - 1;
                    break;
                }

                $seen[$key] = true;
            }

            $run = array_slice($headings, $i, $tocEnd - $i + 1);

            if (count($run) >= 3 && count(array_unique(array_column($run, 'key'))) >= 3) {
                foreach (range($i, $tocEnd) as $index) {
                    $drop[$index] = true;
                }

                $i = $tocEnd + 1;
            } else {
                $i++;
            }
        }

        return array_values(array_diff_key($headings, $drop));
    }

    private function looksLikeTocGap(string $fullText, int $headingStart, int $nextStart): bool
    {
        $lineEnd = strpos($fullText, "\n", $headingStart);
        $bodyStart = $lineEnd === false || $lineEnd > $nextStart ? $nextStart : $lineEnd + 1;
        $body = substr($fullText, $bodyStart, $nextStart - $bodyStart);

        preg_match_all('/\p{L}{2,}/u', $body, $words);

        if (count($words[0]) < self::TOC_ENTRY_MAX_WORDS) {
            return true;
        }

        // Longer TOC stretches list sub-entries ("Statement of the Problem
        // 3") — mostly short lines ending in a page number.
        $lines = array_values(array_filter(array_map('trim', explode("\n", $body)), fn ($l) => $l !== ''));

        if (count($lines) < 2) {
            return false;
        }

        $numbered = count(array_filter($lines, fn ($l) => preg_match('/(\d{1,3}|\b[ivxlc]{1,6})$/iu', $l) === 1 && str_word_count($l) <= 10));

        return $numbered / count($lines) >= 0.6;
    }

    /**
     * Strips what costs prompt tokens without telling the AI anything
     * about the research: running headers/footers (lines repeated on at
     * least half the pages), page-number and dot-leader lines, TOC-style
     * "Title ... 12" lines, figure/table captions, URLs/DOIs and
     * parenthetical citations like "(Santos et al., 2019)". Pure text
     * processing — nothing here calls an AI.
     *
     * @param  string[]  $pages
     */
    private function cleanForPrompt(string $text, array $pages): string
    {
        $repeated = $this->repeatedPageLines($pages);
        $kept = [];

        foreach (preg_split('/\R/u', $text) as $line) {
            $trimmed = trim($line);

            if ($trimmed === ''
                || isset($repeated[mb_strtolower($trimmed)])
                || preg_match('/^(page\s*)?(\d+|[ivxlc]+)(\s*(of|\/)\s*\d+)?$/iu', $trimmed)
                || preg_match('/\.{4,}|(\.\s){4,}/u', $trimmed)
                || preg_match('/^(figure|fig\.|table|plate|chart)\s*\d+/iu', $trimmed)
                || (str_word_count($trimmed) <= 8 && preg_match('/^\p{L}[\p{L}\s,:\'’&-]{2,80}\s+(\d{1,3}|[ivxlc]{1,6})$/iu', $trimmed))) {
                continue;
            }

            $kept[] = $trimmed;
        }

        $text = implode(' ', $kept);
        $text = preg_replace('~\b(https?://|www\.)\S+~iu', '', $text);
        $text = preg_replace('~\b(doi:\s*|10\.\d{4,9}/)\S+~iu', '', $text);
        $text = preg_replace('/\s*\([^()]{0,120}\b(19|20)\d{2}[a-z]?\)/u', '', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * Lines that appear on at least half of the pages (min. 4 pages) — a
     * running header or footer such as the school name or thesis title.
     *
     * @param  string[]  $pages
     * @return array<string, true>
     */
    private function repeatedPageLines(array $pages): array
    {
        if (count($pages) < 4) {
            return [];
        }

        $seenOnPages = [];

        foreach ($pages as $page) {
            $linesOnPage = [];

            foreach (preg_split('/\R/u', (string) $page) as $line) {
                $line = mb_strtolower(trim($line));

                if (mb_strlen($line) >= 3 && mb_strlen($line) <= 100) {
                    $linesOnPage[$line] = true;
                }
            }

            foreach (array_keys($linesOnPage) as $line) {
                $seenOnPages[$line] = ($seenOnPages[$line] ?? 0) + 1;
            }
        }

        $threshold = count($pages) / 2;

        return array_fill_keys(array_keys(array_filter($seenOnPages, fn ($n) => $n >= $threshold)), true);
    }

    /**
     * Public (not just used internally) because App\Services\Research\
     * ChunkingService reuses these two methods, via injection, to resolve
     * the page number each individual chunk actually starts on — rather
     * than duplicating this offset math a second time.
     *
     * @return int[] page index (0-based) => starting char offset in $fullText
     */
    public function pageStartOffsets(array $pages): array
    {
        $starts = [];
        $offset = 0;

        foreach ($pages as $i => $pageText) {
            $starts[$i] = $offset;
            $offset += strlen($pageText) + 1; // +1 for the "\n" joiner
        }

        return $starts;
    }

    /**
     * See pageStartOffsets() docblock — public for the same reason.
     */
    public function pageNumberForOffset(int $offset, array $pageStarts): ?int
    {
        $pageIndex = null;

        foreach ($pageStarts as $i => $start) {
            if ($start <= $offset) {
                $pageIndex = $i;
            } else {
                break;
            }
        }

        return $pageIndex === null ? null : $pageIndex + 1;
    }

    /**
     * A heading candidate is a short, standalone line — not a sentence that
     * happens to contain a heading word. Returns the normalized (uppercased,
     * numbering/punctuation-stripped) line, or null if it can't plausibly be
     * a heading at all.
     */
    private function normalizeHeadingCandidate(string $line): ?string
    {
        $line = trim($line);

        if ($line === '' || mb_strlen($line) > self::MAX_HEADING_LENGTH) {
            return null;
        }

        // Strip leading numbering: "1.", "1.1", "1)", "I.", "A.", etc.
        $line = preg_replace('/^\s*(\d+(\.\d+)*[.)]?|[IVXLC]+\.)\s+/i', '', $line);

        // Strip a trailing colon and surrounding whitespace.
        $line = trim($line, " \t\n\r\0\x0B:");

        if ($line === '') {
            return null;
        }

        return mb_strtoupper($line);
    }

    /**
     * @param  array<int, array{key: string, start: int, end: int}>  $sections
     * @return array{text: ?string, confidence: ?string, word_count: int, keywords: string[]}
     */
    private function extractAbstract(array $sections, string $fullText): array
    {
        $abstractSection = null;

        foreach ($sections as $section) {
            if ($section['key'] === 'abstract') {
                $abstractSection = $section;
                break;
            }
        }

        if ($abstractSection === null) {
            return ['text' => null, 'confidence' => null, 'word_count' => 0, 'keywords' => []];
        }

        // The heading line itself is part of [start, end); skip past it.
        $headingLineEnd = strpos($fullText, "\n", $abstractSection['start']);
        $bodyStart = $headingLineEnd === false ? $abstractSection['start'] : $headingLineEnd + 1;

        $rawBody = trim(substr($fullText, $bodyStart, $abstractSection['end'] - $bodyStart));
        $rawBody = $this->stripCitationHeader($rawBody);

        // A "Keywords:" line commonly sits right after the abstract
        // paragraph but before the next heading — i.e. inside this raw
        // body. Pull it out of the abstract text if present.
        [$text, $keywords] = $this->splitOffKeywordsLine($rawBody);

        $wordCount = $text === '' ? 0 : str_word_count($text);

        $confidence = ($wordCount >= self::ABSTRACT_MIN_WORDS && $wordCount <= self::ABSTRACT_MAX_WORDS)
            ? 'high'
            : 'low';

        return [
            'text' => $text === '' ? null : $text,
            'confidence' => $text === '' ? null : $confidence,
            'word_count' => $wordCount,
            'keywords' => $keywords,
        ];
    }

    /**
     * Splits a "Keywords: ..." (or "Key words:", "Index Terms:") block out
     * of a block of text, returning the remaining text (everything before
     * that line) and the parsed keyword list.
     *
     * The keyword list itself commonly wraps onto the following line(s) in
     * extracted PDF text, and uses separators other than a plain comma
     * (bullets, middots, en/em dashes) — a single-line, comma-only split
     * either truncated the list mid-way or produced one long run-on
     * "keyword" out of the whole block. This captures every line up to the
     * next blank line (a paragraph break) and splits on any of those
     * separators; tokens that still come out implausibly long (a sentence
     * fragment, not a keyword) are discarded rather than added as junk.
     *
     * @return array{0: string, 1: string[]}
     */
    private function splitOffKeywordsLine(string $text): array
    {
        if (! preg_match(
            '/^[ \t]*(?:keywords?|key\s*words?|index\s*terms?)\s*[:\-–]\s*(.+?)(?:\n[ \t]*\n|\z)/msi',
            $text,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            return [$text, []];
        }

        $lineStart = $matches[0][1];
        $remaining = trim(substr($text, 0, $lineStart));

        // Normalize embedded newlines (the block can wrap across lines)
        // to spaces before splitting on the usual list separators —
        // comma, semicolon, bullet, middot, or a dash used as a divider.
        $block = preg_replace('/\s+/', ' ', $matches[1][0]);
        $raw = preg_split('/\s*[,;•·]\s*|\s+[-–—]\s+/u', $block);

        $keywords = [];
        foreach ($raw as $keyword) {
            $keyword = trim($keyword, " \t\n\r\0\x0B.");

            if ($keyword === '' || mb_strlen($keyword) > self::MAX_KEYWORD_LENGTH) {
                continue;
            }

            // A real keyword phrase doesn't end a sentence — a token
            // carrying sentence-ending punctuation is text that ran on
            // past the actual keyword list (the next paragraph, most
            // often) rather than a keyword itself.
            if (preg_match('/[.!?]$/', $keyword)) {
                continue;
            }

            $keywords[] = $keyword;
        }

        return [$remaining, array_values(array_unique($keywords))];
    }

    /**
     * Extract the References/Bibliography section from the PDF text.
     *
     * @param array<int, array{key: string, start: int, end: int}> $sections
     * @return string[]
     */
    private function extractReferences(array $sections, string $fullText): array
    {
        $referencesSection = null;

        foreach ($sections as $section) {
            if ($section['key'] === 'references') {
                $referencesSection = $section;
                break;
            }
        }

        if ($referencesSection === null) {
            return [];
        }

        // Skip the REFERENCES heading itself.
        $headingLineEnd = strpos(
            $fullText,
            "\n",
            $referencesSection['start']
        );

        $bodyStart = $headingLineEnd === false
            ? $referencesSection['start']
            : $headingLineEnd + 1;

        $rawReferences = trim(
            substr(
                $fullText,
                $bodyStart,
                $referencesSection['end'] - $bodyStart
            )
        );

        if ($rawReferences === '') {
            return [];
        }

        // Normalize line endings.
        $rawReferences = preg_replace(
            "/\r\n|\r/",
            "\n",
            $rawReferences
        );

        // Remove excessive blank lines.
        $rawReferences = preg_replace(
            "/\n{2,}/",
            "\n",
            $rawReferences
        );

        $lines = preg_split(
            '/\n/',
            trim($rawReferences)
        );

        $references = [];
        $currentReference = '';

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            /*
             * Detect numbered references:
             *
             * [1] Santos...
             * [2] Garcia...
             * 1. Santos...
             * 2. Garcia...
             * 1) Santos...
             */
            $isNewReference = preg_match(
                '/^(?:\[\d+\]|\d+[\.\)])\s+/u',
                $line
            );

            if ($isNewReference) {
                if ($currentReference !== '') {
                    $references[] = trim($currentReference);
                }

                $currentReference = $line;

                continue;
            }

            /*
             * If the document uses APA/author-date style without numbering,
             * detect a likely new reference by an author surname followed
             * by initials and a year.
             *
             * Example:
             * Santos, J. P. (2023)...
             * Garcia, M. A. (2022)...
             */
            $looksLikeAuthorDateReference = preg_match(
                '/^[A-ZÀ-Ý][A-Za-zÀ-ÿ\'\-]+(?:,\s*[A-ZÀ-Ý](?:\.[A-ZÀ-Ý]?)*\.?)*.*?\(\d{4}[a-z]?\)/u',
                $line
            );

            if ($looksLikeAuthorDateReference && $currentReference !== '') {
                $references[] = trim($currentReference);
                $currentReference = $line;

                continue;
            }

            // Continuation of the previous reference.
            if ($currentReference !== '') {
                $currentReference .= ' '.$line;
            } else {
                $currentReference = $line;
            }
        }

        if ($currentReference !== '') {
            $references[] = trim($currentReference);
        }

        return array_values(
            array_unique(
                array_filter($references)
            )
        );
    }

    /**
     * Source text for AI abstract generation (App\Services\Ai\
     * AbstractGenerationService) when the paper has no detectable
     * abstract: the opening words of its introduction, methodology,
     * results and conclusion — enough to summarize purpose, method,
     * findings and conclusion without sending the whole thesis, cleaned
     * of headers, page numbers and citations (cleanForPrompt()). When none
     * of those headings were found, falls back to the body text after any
     * front matter (approval sheet, acknowledgments, table of contents).
     * Deterministic; nothing here calls an AI.
     *
     * @param  array<int, array{key: string, heading: string, start: int, end: int, page_number: ?int}>  $sections  detect()'s 'sections'
     * @param  string[]  $pages  per-page text, for header/footer detection
     */
    public function abstractSourceExcerpt(array $sections, string $fullText, array $pages = []): ?string
    {
        $parts = [];

        // A combined "RESULTS AND DISCUSSION" heading already covers the
        // discussion — don't spend a second budget on the same text.
        $resultsCoverDiscussion = (bool) array_filter(
            $sections,
            fn ($s) => $s['key'] === 'results' && str_contains(mb_strtoupper($s['heading']), 'DISCUSSION')
        );

        foreach (self::ABSTRACT_SOURCE_SECTIONS as $key => [$label, $words]) {
            if ($key === 'discussion' && $resultsCoverDiscussion) {
                continue;
            }

            $body = $this->sectionBody($sections, $key, $fullText);
            $body = $body === null ? '' : $this->cleanForPrompt($body, $pages);

            if ($body !== '') {
                $parts[] = "[{$label}]\n".$this->firstWords($body, $words);
            }
        }

        if ($parts !== []) {
            return implode("\n\n", $parts);
        }

        $body = $this->cleanForPrompt($this->skipFrontMatter($fullText, $pages), $pages);

        return $body === '' ? null : $this->firstWords($body, self::ABSTRACT_SOURCE_FALLBACK_WORDS);
    }

    /**
     * The text after the last front-matter heading (approval sheet,
     * dedication, acknowledgments, table of contents, lists of
     * tables/figures) found in the first half of the document — or the
     * whole text if there is none. Front matter is a page of its own in a
     * thesis, so with page texts available the cut moves on to the next
     * page, skipping that heading's body too (the acknowledgment itself,
     * not only its title).
     *
     * @param  string[]  $pages
     */
    private function skipFrontMatter(string $fullText, array $pages = []): string
    {
        $limit = (int) (strlen($fullText) / 2);
        $cut = 0;

        foreach (preg_split('/\n/', $fullText, -1, PREG_SPLIT_OFFSET_CAPTURE) as [$line, $offset]) {
            if ($offset > $limit) {
                break;
            }

            if (in_array($this->normalizeHeadingCandidate($line), self::FRONT_MATTER_HEADINGS, true)) {
                $cut = $offset + strlen($line) + 1;
            }
        }

        if ($cut > 0 && $pages !== []) {
            $pageStarts = $this->pageStartOffsets($pages);
            $page = $this->pageNumberForOffset($cut - 1, $pageStarts); // 1-based
            $nextPageStart = $pageStarts[$page] ?? null;

            if ($nextPageStart !== null) {
                $cut = $nextPageStart;
            }
        }

        return substr($fullText, $cut);
    }

    /**
     * The text under every heading of one section key, from the first such
     * heading to the end of the last. 'introduction' covers both the bare
     * "INTRODUCTION" heading and its subheadings (e.g. "1.1 Background of
     * the Study" normalizes to "BACKGROUND OF THE STUDY", also an alias —
     * see ALIASES above). A document commonly has a bare "INTRODUCTION"
     * heading with nothing under it before the next (sub)heading
     * immediately follows — using only the first match's span would then
     * extract an empty body. Spanning from the first to the last section
     * with the key instead captures the actual prose, wherever among
     * those subheadings it starts.
     */
    private function sectionBody(array $sections, string $key, string $fullText): ?string
    {
        $firstIndex = null;
        $lastIndex = null;

        foreach ($sections as $i => $section) {
            if ($section['key'] !== $key) {
                continue;
            }

            $firstIndex ??= $i;
            $lastIndex = $i;
        }

        if ($firstIndex === null) {
            return null;
        }

        $section = $sections[$firstIndex];
        $spanEnd = $sections[$lastIndex]['end'];

        $headingLineEnd = strpos($fullText, "\n", $section['start']);
        $bodyStart = $headingLineEnd === false ? $section['start'] : $headingLineEnd + 1;

        $body = trim(substr($fullText, $bodyStart, $spanEnd - $bodyStart));

        return $body === '' ? null : $body;
    }

    private function firstWords(string $text, int $count): string
    {
        return implode(' ', array_slice(preg_split('/\s+/', trim($text)), 0, $count));
    }

    /**
     * A short excerpt from the introduction, used only as a fallback for
     * the AI metadata call when the abstract is missing or too short to
     * classify reliably on its own (MetadataAnalysisService's
     * $fallbackExcerpt parameter — see its docblock). Deterministic, not
     * sent anywhere unless the abstract already failed its own confidence
     * check.
     */
    private function extractFallbackExcerpt(array $sections, string $fullText, array $pages): ?string
    {
        $body = $this->sectionBody($sections, 'introduction', $fullText);

        if ($body === null) {
            return null;
        }

        $body = $this->cleanForPrompt($body, $pages);

        $words = preg_split('/\s+/', $body);
        $excerpt = implode(' ', array_slice($words, 0, self::FALLBACK_EXCERPT_WORDS));

        return $excerpt === '' ? null : $excerpt;
    }

    /**
     * Many Philippine university thesis abstract pages open with a
     * citation-style header directly under the "ABSTRACT" heading —
     * author names (commonly written fully in capitals, e.g. "CARILLO,
     * PRINCESS MAE I., CORPUZ, LIMBERT A."), followed by the college/
     * university, term, thesis title, and adviser — before the actual
     * abstract paragraph begins. Left in, that header (not the abstract)
     * is what a student sees pre-filled and what gets sent to the AI
     * metadata call, producing keyword/SDG suggestions unrelated to the
     * actual research.
     *
     * Only strips when all of these hold: the body opens with a run of
     * 20+ uppercase/comma/period characters with no lowercase letter (a
     * plain length/case check alone isn't enough — a real abstract can
     * legitimately open with a long capitalized phrase, e.g. an acronym-
     * led sentence); that run contains at least two commas AND at least
     * one bare single-letter initial followed by a period (e.g. "I.",
     * "A.") — the combination of multiple comma-separated entries plus
     * period-terminated initials is essentially unique to this citation
     * convention and not to narrative prose; AND a recognizable
     * narrative abstract opener ("This/The study/research/...") is found
     * further in, giving a safe place to resume from. If any signal is
     * missing, the body is returned unchanged rather than guessing wrong
     * on a document that doesn't use this convention.
     */
    private function stripCitationHeader(string $body): string
    {
        if (! preg_match('/^([A-ZÀ-Ý0-9,.\'\-\s]{20,})/u', $body, $prefixMatch)) {
            return $body;
        }

        $prefix = $prefixMatch[1];
        $hasMultipleEntries = substr_count($prefix, ',') >= 2;
        $hasInitial = (bool) preg_match('/\b[A-ZÀ-Ý]\.\s*,?/u', $prefix);

        if (! $hasMultipleEntries || ! $hasInitial) {
            return $body;
        }

        // No /i flag deliberately: "This"/"The" must be capitalized to
        // count as a sentence start. A case-insensitive match here would
        // also fire on a lowercase "...and the study is believed to..."
        // occurring mid-sentence, cutting the real abstract's opening
        // clause off instead of landing on its actual first sentence.
        if (preg_match(
            '/\b(?:This|The)\s+(?:study|research|paper|thesis|investigation)\b/u',
            $body,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            return trim(substr($body, $matches[0][1]));
        }

        return $body;
    }
}
