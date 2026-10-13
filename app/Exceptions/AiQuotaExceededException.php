<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a student has used up today's submission-AI quota
 * (App\Support\SubmissionAiQuota). The message is user-facing.
 */
class AiQuotaExceededException extends RuntimeException
{
    public static function forLimit(int $limit): self
    {
        return new self("You've reached today's limit of {$limit} AI uses for submissions. You can still enter your abstract and keywords manually, or try again tomorrow.");
    }
}
