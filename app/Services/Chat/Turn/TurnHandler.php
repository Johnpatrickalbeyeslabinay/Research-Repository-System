<?php

namespace App\Services\Chat\Turn;

use App\Services\Chat\Routing\Intent;

/**
 * Handles one kind of turn.
 *
 * The contract, and the reason it is worth stating: a handler PERSISTS NOTHING
 * and CALLS NO LLM. It returns a TurnOutcome describing what should happen, and
 * ChatTurnService does every side effect.
 *
 * That gives a structural guarantee no amount of reviewing a branch chain could:
 * a turn cannot make two LLM calls, because handlers cannot make even one.
 */
interface TurnHandler
{
    /** @return Intent[] */
    public function handles(): array;

    public function handle(ChatTurn $turn): TurnOutcome;
}
