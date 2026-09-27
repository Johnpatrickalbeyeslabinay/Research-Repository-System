<?php

namespace App\Services\Chat\Turn;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\Chat\Routing\RoutedIntent;

/**
 * Everything a handler is given for one turn.
 *
 * $userMessage is already persisted when a handler runs — deliberately, so a
 * provider timeout costs the user their answer and not their typing. Handlers
 * need its id to exclude the current question from the history window (see
 * PromptBuilder::trimmedHistory), and occasionally to mark it out of history
 * entirely on a zero-token turn.
 */
final class ChatTurn
{
    public function __construct(
        public readonly RoutedIntent $routed,
        public readonly ChatConversation $conversation,
        public readonly ChatMessage $userMessage,
        public readonly ?User $user,
        public readonly string $requestId,
    ) {}

    /** Greeting-stripped text: what retrieval and the prompt work on. */
    public function question(): string
    {
        return $this->routed->question;
    }

    public function isPinned(): bool
    {
        return $this->conversation->stud_file_id !== null;
    }
}
