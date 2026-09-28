<?php

namespace App\Services\Chat\Handlers;

use App\Services\Chat\Routing\Intent;
use App\Services\Chat\SmallTalk\CannedReplies;
use App\Services\Chat\Turn\ChatTurn;
use App\Services\Chat\Turn\TurnHandler;
use App\Services\Chat\Turn\TurnOutcome;

/**
 * Greetings, thanks, farewells and gibberish — answered from a constant.
 *
 * Both rows leave the prompt history. Neither "hi" nor "Hello! Ask me about…"
 * tells a later turn anything, and together they would burn 2 of the 8
 * available history slots. They stay in the TRANSCRIPT — exclusion is
 * prompt-only, and the user saw a reply, so their history has to show one.
 */
final class SmallTalkHandler implements TurnHandler
{
    public function __construct(private CannedReplies $replies) {}

    public function handles(): array
    {
        return [Intent::SmallTalk, Intent::Acknowledgement, Intent::Unintelligible];
    }

    public function handle(ChatTurn $turn): TurnOutcome
    {
        $conversation = $turn->conversation;

        $content = match ($turn->routed->intent) {
            Intent::SmallTalk => $this->replies->greeting(
                $conversation,
                $conversation->studFile?->title,
                $this->isFilipino($turn->question()),
            ),
            Intent::Acknowledgement => $this->replies->acknowledgement($conversation),
            default => $this->replies->unintelligible($conversation),
        };

        return TurnOutcome::canned(
            content: $content,
            retrievalMeta: $turn->routed->toMeta(),
            excludeUserMessage: true,
        );
    }

    private function isFilipino(string $message): bool
    {
        return (bool) preg_match('/\b(kamusta|kumusta|kmusta|musta|magandang|naimbag|salamat)\b/i', $message);
    }
}
