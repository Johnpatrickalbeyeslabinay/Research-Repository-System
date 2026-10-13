<?php

namespace App\Services\Pdf;

/**
 * Decides whether an uploaded PDF is a scanned / image-only document,
 * deterministically, from what PdfTextExtractor already produced — no
 * extra parse and no AI.
 *
 * PdfTextExtractor on its own only rejects a PDF whose text is entirely
 * empty, which misses the common real-world scans: a phone-scanner export
 * stamps "Scanned with CamScanner" on every page, a scanned thesis often
 * has an OCR'd or typed cover page, and page-number footers survive as
 * text. All of those used to pass validation and then yield no abstract,
 * junk keyword suggestions and empty search chunks. Here a page counts as
 * image-like when it carries an embedded image AND has almost no
 * meaningful text once that scanner noise is stripped out.
 *
 * There is no OCR fallback on purpose: the app runs on serverless PHP
 * (vercel.json), which has no Tesseract and no PDF-to-image renderer, so
 * the honest response to a scan is to reject it with a clear message.
 */
class ScannedPdfDetector
{
    public const VERDICT_TEXT = 'text';

    public const VERDICT_PARTIAL = 'partial';

    public const VERDICT_SCANNED = 'scanned';

    // Below this many meaningful characters, a page with an image on it is
    // treated as a picture of text rather than text. A real text page
    // carries well over a thousand; a scanner watermark plus a page number
    // is a few dozen.
    private const MIN_MEANINGFUL_CHARS = 80;

    // Stamps phone/desktop scanner apps add to each page. Matched
    // case-insensitively and removed before measuring a page's text.
    private const SCANNER_WATERMARKS = [
        '/scanned\s+(with|by|using)\s+[\p{L}\p{N} ]{0,40}/iu',
        '/\bcam\s*scanner\b/iu',
        '/\badobe\s+scan\b/iu',
        '/\bgenius\s+scan\b/iu',
        '/\bmicrosoft\s+lens\b/iu',
        '/\boffice\s+lens\b/iu',
        '/\btiny\s*scanner\b/iu',
    ];

    /**
     * @param  string[]  $pages  per-page text, as PdfTextExtractor returns it
     * @param  bool[]  $pageHasImage  per-page "has an image XObject" flags, same order
     * @return array{image_pages: int, text_pages: int, page_count: int, ratio: float, verdict: string}
     */
    public function detect(array $pages, array $pageHasImage): array
    {
        $imagePages = 0;

        foreach (array_values($pages) as $i => $text) {
            if (($pageHasImage[$i] ?? false) && $this->meaningfulLength($text) < self::MIN_MEANINGFUL_CHARS) {
                $imagePages++;
            }
        }

        $pageCount = count($pages);
        $ratio = $pageCount > 0 ? $imagePages / $pageCount : 0.0;

        return [
            'image_pages' => $imagePages,
            'text_pages' => $pageCount - $imagePages,
            'page_count' => $pageCount,
            'ratio' => round($ratio, 2),
            'verdict' => $this->verdictFor($ratio, $imagePages),
        ];
    }

    private function verdictFor(float $ratio, int $imagePages): string
    {
        if ($imagePages === 0) {
            return self::VERDICT_TEXT;
        }

        if ($ratio >= (float) config('research.scan_detection.scanned_ratio', 0.6)) {
            return self::VERDICT_SCANNED;
        }

        if ($ratio >= (float) config('research.scan_detection.partial_ratio', 0.2)) {
            return self::VERDICT_PARTIAL;
        }

        return self::VERDICT_TEXT;
    }

    /**
     * Characters of real content on a page: scanner stamps and lines that
     * are only page numbers/punctuation are dropped, and whitespace isn't
     * counted at all.
     */
    private function meaningfulLength(string $text): int
    {
        $text = preg_replace(self::SCANNER_WATERMARKS, '', $text) ?? $text;

        $lines = preg_split('/\R/u', $text) ?: [];
        $kept = array_filter($lines, fn ($line) => preg_match('/\p{L}/u', $line) === 1);

        return mb_strlen(preg_replace('/\s+/u', '', implode('', $kept)) ?? '');
    }
}
