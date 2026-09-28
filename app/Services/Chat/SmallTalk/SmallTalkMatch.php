<?php

namespace App\Services\Chat\SmallTalk;

/**
 * The verdict on one message.
 *
 * $residual is the load-bearing field. When a message opens with a greeting
 * but continues into a real question ("hi, what research is there about
 * tilapia?"), this is NOT small talk — but the remainder is what the rest of
 * the pipeline should work on, and $greeting records that a hello was seen so
 * a handler can acknowledge it for free rather than ignoring it.
 *
 * Keeping "is small talk" and "had a greeting prefix" as separate facts is
 * what prevents the single worst regression available here: answering
 * "hi, what research is there about tilapia?" with nothing but "Hello!".
 */
final class SmallTalkMatch
{
    public const GREETING = 'greeting';

    public const ACKNOWLEDGEMENT = 'acknowledgement';

    public const FAREWELL = 'farewell';

    public const UNINTELLIGIBLE = 'unintelligible';

    public const NONE = 'none';

    private function __construct(
        /** One of the constants above. */
        public readonly string $category,
        /** True only when the WHOLE message was small talk. */
        public readonly bool $isWholeMessage,
        /** The message with any leading small-talk span removed. */
        public readonly string $residual,
        /** Why it matched — 'lexicon', 'phrase', 'fuzzy', 'emoji'; for logging. */
        public readonly string $method = 'lexicon',
    ) {}

    public static function wholeMessage(string $category, string $method = 'lexicon'): self
    {
        return new self($category, true, '', $method);
    }

    /** A greeting prefix followed by real content. */
    public static function prefixOnly(string $category, string $residual, string $method = 'lexicon'): self
    {
        return new self($category, false, $residual, $method);
    }

    public static function none(string $residual): self
    {
        return new self(self::NONE, false, $residual, 'none');
    }

    /** True when this turn can be answered without touching the API. */
    public function isZeroToken(): bool
    {
        return $this->isWholeMessage;
    }

    public function isGreeting(): bool
    {
        return $this->isWholeMessage && $this->category === self::GREETING;
    }

    public function isAcknowledgement(): bool
    {
        return $this->isWholeMessage
            && in_array($this->category, [self::ACKNOWLEDGEMENT, self::FAREWELL], true);
    }

    /** A greeting prefix was stripped and real content remains. */
    public function hasStrippedPrefix(): bool
    {
        return ! $this->isWholeMessage && $this->category !== self::NONE;
    }
}
