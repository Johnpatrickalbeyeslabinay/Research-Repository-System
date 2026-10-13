<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an AI abstract draft can't be produced. The message is
 * user-facing — the student can always type the abstract themselves, so
 * this never blocks submission.
 */
class AbstractGenerationException extends RuntimeException
{
    public static function unavailable(): self
    {
        return new self('AI abstract generation is temporarily unavailable. Please write the abstract manually.');
    }

    public static function invalidResponse(): self
    {
        return new self('An abstract could not be generated for this PDF. Please write the abstract manually.');
    }

    public static function notConfigured(): self
    {
        return new self('AI abstract generation is not enabled for this installation. Please write the abstract manually.');
    }

    public static function sourceExpired(): self
    {
        return new self('Your PDF session expired. Please re-select your PDF, then try generating the abstract again.');
    }
}
