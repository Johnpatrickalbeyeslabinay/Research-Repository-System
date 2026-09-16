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
    public function __construct(private PdfTextExtractor $extractor) {}

    /**
     * @return array{hash: string, full_text: string, pages: string[], page_count: int}
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

        return array_merge(['hash' => $hash], $extracted);
    }
}
