<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when the metadata AI call fails or returns something that doesn't
 * validate against the expected schema. The message is user-facing —
 * callers should fall back to manual keyword/SDG entry, never block
 * submission on this.
 */
class MetadataAnalysisException extends RuntimeException
{
    public static function unavailable(): self
    {
        return new self('AI suggestions are temporarily unavailable. You can continue by entering keywords and SDGs manually.');
    }

    public static function invalidResponse(): self
    {
        return new self('AI suggestions could not be generated for this research. You can continue by entering keywords and SDGs manually.');
    }

    public static function notConfigured(): self
    {
        return new self('AI suggestions are not enabled for this installation. You can continue by entering keywords and SDGs manually.');
    }
}
