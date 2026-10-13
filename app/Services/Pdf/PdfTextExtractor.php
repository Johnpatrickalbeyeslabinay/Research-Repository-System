<?php

namespace App\Services\Pdf;

use App\Exceptions\PdfProcessingException;
use Illuminate\Support\Facades\Cache;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\XObject\Image;

/**
 * Thin wrapper around smalot/pdfparser. Deterministic text extraction only
 * — no generative AI involved anywhere in this class.
 */
class PdfTextExtractor
{
    // Bumped whenever extract()'s output changes shape/content for the
    // same PDF (e.g. the UTF-8 sanitization added below) — invalidates any
    // already-cached extraction from before the change, since a cached
    // result from a buggy version would otherwise keep being served as-is
    // for the rest of its TTL regardless of the code fix.
    // v3: page_has_image added for ScannedPdfDetector.
    private const CACHE_VERSION = 'v3';

    /**
     * Same as extract(), but cached by content hash. A single submission
     * previously re-parsed the identical PDF up to three times — once when
     * the file is picked (analyzePdf), once on the real submit (store()),
     * and once more in the background indexing job — each a full
     * smalot/pdfparser pass over the whole document. Caching by hash
     * collapses that to one parse; the other two calls become cache hits.
     *
     * @return array{full_text: string, pages: string[], page_has_image: bool[], page_count: int}
     *
     * @throws PdfProcessingException
     */
    public function extractCached(string $hash, string $absolutePath): array
    {
        return Cache::remember(
            self::CACHE_VERSION.":pdf_extract:{$hash}",
            now()->addMinutes(30),
            fn () => $this->extract($absolutePath)
        );
    }

    /**
     * @return array{full_text: string, pages: string[], page_has_image: bool[], page_count: int}
     *
     * @throws PdfProcessingException
     */
    public function extract(string $absolutePath): array
    {
        try {
            $document = (new Parser)->parseFile($absolutePath);
            $pages = $document->getPages();
        } catch (\Throwable $e) {
            if (str_contains(strtolower($e->getMessage()), 'secured')) {
                throw PdfProcessingException::encrypted();
            }

            throw PdfProcessingException::corrupted();
        }

        $pageTexts = [];
        $pageHasImage = [];

        foreach ($pages as $page) {
            try {
                $pageTexts[] = $this->sanitizeUtf8(trim((string) $page->getText()));
            } catch (\Throwable) {
                // A single unreadable page shouldn't sink the whole document.
                $pageTexts[] = '';
            }

            $pageHasImage[] = $this->pageHasImage($page);
        }

        $fullText = implode("\n", $pageTexts);

        if (trim($fullText) === '') {
            $imagePages = count(array_filter($pageHasImage));

            // No text at all but pictures on the pages is a scan — say so,
            // rather than the vaguer "no extractable text".
            throw $imagePages > 0
                ? PdfProcessingException::scanned($imagePages, count($pageTexts))
                : PdfProcessingException::noExtractableText();
        }

        return [
            'full_text' => $fullText,
            'pages' => $pageTexts,
            'page_has_image' => $pageHasImage,
            'page_count' => count($pageTexts),
        ];
    }

    /**
     * Whether the page embeds at least one raster image — the signal
     * ScannedPdfDetector pairs with a near-empty page text to call a page
     * a scan. A page whose resources can't be read counts as no image, so
     * a parser quirk can only make detection more lenient, never reject a
     * real text PDF.
     */
    private function pageHasImage(Page $page): bool
    {
        try {
            foreach ($page->getXObjects() as $xObject) {
                if ($xObject instanceof Image) {
                    return true;
                }
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    /**
     * PDFs using custom/symbol fonts or ligatures occasionally decode
     * through smalot/pdfparser as byte sequences that aren't valid UTF-8.
     * Left uncleaned, that breaks anything downstream that assumes valid
     * UTF-8 text — json_encode() (used by analyzePdf()'s JSON response)
     * throws "Malformed UTF-8 characters, possibly incorrectly encoded"
     * and aborts the request entirely, and a Postgres text/jsonb column
     * write would fail outright. Invalid byte sequences are dropped;
     * everything else is kept as-is.
     */
    private function sanitizeUtf8(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $text);

        return $cleaned === false ? mb_convert_encoding($text, 'UTF-8', 'UTF-8') : $cleaned;
    }
}
