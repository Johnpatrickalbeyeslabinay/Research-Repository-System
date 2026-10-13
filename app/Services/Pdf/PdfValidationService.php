<?php

namespace App\Services\Pdf;

use App\Exceptions\PdfProcessingException;
use Illuminate\Http\UploadedFile;

/**
 * Validates an uploaded research PDF and extracts its text, deterministically
 * — no generative AI is used at this stage. Extension/MIME/size are already
 * enforced by the request's validation rules (mimes:pdf|max:10240); this
 * service handles what those rules can't: is the file actually a readable,
 * unencrypted PDF with extractable text.
 */
class PdfValidationService
{
    public function __construct(
        private PdfTextExtractor $extractor,
        private ScannedPdfDetector $scanDetector,
    ) {}

    /**
     * Rejects a mostly-scanned PDF outright; a partly-scanned one passes
     * with its 'scan' summary attached so the form can warn about it.
     *
     * @return array{hash: string, full_text: string, pages: string[], page_has_image: bool[], page_count: int, scan: array{image_pages: int, text_pages: int, page_count: int, ratio: float, verdict: string}}
     *
     * @throws PdfProcessingException
     */
    public function process(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'pdf' || $file->getMimeType() !== 'application/pdf') {
            throw PdfProcessingException::corrupted();
        }

        $hash = hash_file('sha256', $file->getRealPath());

        $extracted = $this->extractor->extractCached($hash, $file->getRealPath());

        $scan = $this->scanDetector->detect($extracted['pages'], $extracted['page_has_image'] ?? []);

        if ($scan['verdict'] === ScannedPdfDetector::VERDICT_SCANNED) {
            throw PdfProcessingException::scanned($scan['image_pages'], $scan['page_count']);
        }

        return array_merge(['hash' => $hash], $extracted, ['scan' => $scan]);
    }
}
