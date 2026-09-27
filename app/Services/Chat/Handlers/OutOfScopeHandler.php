<?php

namespace App\Services\Chat\Handlers;

use App\Services\Chat\Routing\Intent;
use App\Services\Chat\SmallTalk\CannedReplies;
use App\Services\Chat\Turn\ChatTurn;
use App\Services\Chat\Turn\TurnHandler;
use App\Services\Chat\Turn\TurnOutcome;

/**
 * Clearly off-domain requests, declined in one sentence and redirected.
 *
 * Exists because of a live bug: "recommend a good restaurant" matches
 * IntentDetector's /\b(recommend|suggest)\b/i, so it reached CatalogService and
 * spent a real LLM call listing fisheries papers in reply to a question about
 * dinner. The classifier that routes here only fires when an off-domain term is
 * present AND no domain term is — refusing a real research question would be
 * far worse than answering an off-topic one.
 */
final class OutOfScopeHandler implements TurnHandler
{
    public function __construct(private CannedReplies $replies) {}

    public function handles(): array
    {
        return [Intent::OutOfScope];
    }

    public function handle(ChatTurn $turn): TurnOutcome
    {
        return TurnOutcome::canned(
            content: $this->replies->outOfScope($turn->conversation),
            retrievalMeta: $turn->routed->toMeta(),
        );
    }
}
