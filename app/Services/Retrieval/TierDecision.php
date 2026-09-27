<?php

namespace App\Services\Retrieval;

/**
 * A tier plus why it was chosen.
 *
 * The reason is not decoration. Tier distribution is the number that tells you
 * whether abstract-first actually paid off, and "everything escalated to full
 * text" and "everything stayed at abstract" both look identical in a token
 * total until you can see WHY each turn landed where it did. It is written to
 * retrieval_meta and reported per fixture by research:eval-chat.
 */
final class TierDecision
{
    public function __construct(
        public readonly Tier $tier,
        public readonly string $reason,
        /** True when this came from EvidenceSufficiencyGate rather than the planner. */
        public readonly bool $escalated = false,
    ) {}

    public static function escalate(Tier $to, string $reason): self
    {
        return new self($to, $reason, escalated: true);
    }

    public function toMeta(): array
    {
        return [
            'tier' => $this->tier->value,
            'tier_reason' => $this->reason,
            'tier_escalated' => $this->escalated,
        ];
    }
}
