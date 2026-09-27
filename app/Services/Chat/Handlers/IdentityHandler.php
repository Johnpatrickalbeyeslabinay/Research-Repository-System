<?php

namespace App\Services\Chat\Handlers;

use App\Services\Chat\Routing\Intent;
use App\Services\Chat\SmallTalk\CannedReplies;
use App\Services\Chat\Turn\ChatTurn;
use App\Services\Chat\Turn\TurnHandler;
use App\Services\Chat\Turn\TurnOutcome;

/**
 * "Who are you", "what can you do", "are you ChatGPT".
 *
 * These matched none of PlatformQuestionDetector's patterns, so every one used
 * to run full research retrieval — an embedding call plus a ~2,500-token answer
 * call — to produce a sentence the application already knows.
 */
final class IdentityHandler implements TurnHandler
{
    public function __construct(private CannedReplies $replies) {}

    public function handles(): array
    {
        return [Intent::Identity];
    }

    public function handle(ChatTurn $turn): TurnOutcome
    {
        return TurnOutcome::canned(
            content: $this->replies->identity($turn->conversation),
            retrievalMeta: $turn->routed->toMeta(),
        );
    }
}
