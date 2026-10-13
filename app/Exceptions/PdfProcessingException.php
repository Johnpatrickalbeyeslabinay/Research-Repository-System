<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an uploaded PDF fails validation (password-protected,
 * corrupted, or has no extractable text). The message is user-facing and
 * safe to show directly in the submission form.
 */
class PdfProcessingException extends RuntimeException
{
    public static function encrypted(): self
    {
        return new self('This PDF is password protected. Please upload an unlocked copy.');
    }

    public static function corrupted(): self
    {
        return new self('We could not read this PDF. It may be corrupted.');
    }

    public static function noExtractableText(): self
    {
        return new self('We could not extract readable text from this PDF. If it is a scanned/image-only document, please upload a text-based PDF instead.');
    }

    public static function scanned(int $imagePages, int $pageCount): self
    {
        return new self("This PDF appears to be a scanned/image-only document ({$imagePages} of {$pageCount} pages are images). Please upload a text-based PDF — export it from Word/Google Docs, or run OCR on the scan first.");
    }
}
