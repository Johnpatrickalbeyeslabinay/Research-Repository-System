<?php

namespace App\Services\Chat\Handlers;

use App\Services\Chat\PromptBuilder;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Turn\ChatTurn;
use App\Services\Chat\Turn\TurnHandler;
use App\Services\Chat\Turn\TurnOutcome;

/**
 * Questions about the application itself.
 *
 * Answered from PromptBuilder::PLATFORM_INFO only — no retrieval, no
 * REPOSITORY EVIDENCE block at all. Retrieval is skipped ENTIRELY here (not
 * just the LLM call, unlike the no-evidence path) because running it anyway
 * risks exactly the contamination this path exists to prevent: a
 * weakly-matching paper's evidence getting attached to a question that has
 * nothing to do with any specific paper. Verified live — that is precisely what
 * was happening before this path existed.
 */
final class PlatformHandler implements TurnHandler
{
    public function __construct(private PromptBuilder $prompts) {}

    public function handles(): array
    {
        return [Intent::Platform];
    }

    public function handle(ChatTurn $turn): TurnOutcome
    {
        $prompt = $this->prompts->buildPlatformOnly(
            $turn->conversation,
            $turn->question(),
            $turn->userMessage->id,
        );

        return TurnOutcome::answered(
            prompt: $prompt,
            retrievalMeta: $turn->routed->toMeta() + [
                'prompt_version' => PromptBuilder::promptVersion(),
            ],
        );
    }
}
