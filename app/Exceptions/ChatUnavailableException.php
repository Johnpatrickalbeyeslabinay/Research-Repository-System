<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when the chatbot's answer-generation call fails, times out, or
 * returns something unusable. The message is user-facing — the chat UI
 * shows it with a Retry action. The user's question is always persisted
 * before this can be thrown (see ChatController::message()), so nothing is
 * lost when it fires.
 */
class ChatUnavailableException extends RuntimeException
{
    public static function notConfigured(): self
    {
        return new self('The AI research assistant is not enabled for this installation.');
    }

    public static function providerError(): self
    {
        return new self('The AI research assistant is temporarily unavailable. Please try again in a moment.');
    }

    public static function malformedResponse(): self
    {
        return new self('The AI research assistant returned an unexpected response. Please try again.');
    }
}
