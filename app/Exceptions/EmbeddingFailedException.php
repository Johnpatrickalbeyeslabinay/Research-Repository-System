<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an embedding call fails, times out, or returns something
 * unusable. The message is diagnostic — it ends up in
 * stud_file.search_index_error and in research:check-ai output — but it
 * carries only the HTTP status and OpenAI's error code, never the raw
 * response body. The body is logged server-side instead (see
 * App\Services\Research\EmbeddingService).
 *
 * Not user-facing in chat: ChatTurnService maps it onto
 * ChatUnavailableException::providerError().
 */
class EmbeddingFailedException extends RuntimeException
{
    public static function notConfigured(): self
    {
        return new self('OpenAI API key is not configured.');
    }

    public static function requestFailed(int $status, ?string $code): self
    {
        return new self("Embedding request failed (HTTP {$status}".($code ? ": {$code}" : '').').');
    }

    public static function unreachable(): self
    {
        return new self('Embedding request failed: the provider could not be reached.');
    }

    public static function malformedResponse(): self
    {
        return new self('Unexpected embedding response shape.');
    }
}
