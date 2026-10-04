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
    private const CACHE_VERSION = 'v6';

    // A parsed keyword token this long is almost certainly a mis-split
    // sentence fragment (e.g. a "Keywords:" block that runs into the next
    // paragraph), not an actual keyword — discard rather than add it as one.
    private const MAX_KEYWORD_LENGTH = 60;

    // How many words of the introduction section to use as the AI's
    // fallback excerpt when the abstract itself is missing or too short
    // (see MetadataAnalysisService's $fallbackExcerpt parameter).
    private const FALLBACK_EXCERPT_WORDS = 300;

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
            'fallback_excerpt' => $this->extractFallbackExcerpt($sections, $fullText),
        ];
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
     * A short excerpt from the introduction, used only as a fallback for
     * the AI metadata call when the abstract is missing or too short to
     * classify reliably on its own (MetadataAnalysisService's
     * $fallbackExcerpt parameter — see its docblock). Deterministic, not
     * sent anywhere unless the abstract already failed its own confidence
     * check.
     */
    private function extractFallbackExcerpt(array $sections, string $fullText): ?string
    {
        $firstIntroIndex = null;
        $lastIntroIndex = null;

        // 'introduction' covers both the bare "INTRODUCTION" heading and
        // its subheadings (e.g. "1.1 Background of the Study" normalizes
        // to "BACKGROUND OF THE STUDY", also an alias — see ALIASES
        // above). A document commonly has a bare "INTRODUCTION" heading
        // with nothing under it before the next (sub)heading immediately
        // follows — using only the first match's span would then extract
        // an empty body. Spanning from the first to the last contiguous
        // 'introduction'-keyed section instead captures the actual prose,
        // wherever among those subheadings it starts.
        foreach ($sections as $i => $section) {
            if ($section['key'] !== 'introduction') {
                continue;
            }

            $firstIntroIndex ??= $i;
            $lastIntroIndex = $i;
        }

        if ($firstIntroIndex === null) {
            return null;
        }

        $introSection = $sections[$firstIntroIndex];
        $spanEnd = $sections[$lastIntroIndex]['end'];

        $headingLineEnd = strpos($fullText, "\n", $introSection['start']);
        $bodyStart = $headingLineEnd === false ? $introSection['start'] : $headingLineEnd + 1;

        $body = trim(substr($fullText, $bodyStart, $spanEnd - $bodyStart));

        if ($body === '') {
            return null;
        }

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
