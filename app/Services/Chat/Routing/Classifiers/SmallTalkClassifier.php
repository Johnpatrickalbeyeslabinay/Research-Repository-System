<?php

namespace App\Services\Chat\Routing\Classifiers;

use App\Services\Chat\Routing\ClassificationInput;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Routing\RoutedIntent;
use App\Services\Chat\Routing\TurnClassifier;
use App\Services\Chat\SmallTalk\SmallTalkDetector;
use App\Services\Chat\SmallTalk\SmallTalkMatch;

/**
 * Greetings, thanks, farewells and gibberish — the free paths.
 *
 * Runs first, but claims a turn only when the WHOLE message was small talk.
 * SmallTalkDetector's residual rule is what makes that safe: a greeting with
 * real content after it ("hi, what research is there about tilapia?") is
 * reported as a stripped prefix instead, and the router re-routes on the
 * remainder. Answering that message with a bare "Hello!" would be a far worse
 * bug than the tokens this saves.
 */
final class SmallTalkClassifier implements TurnClassifier
{
    public function __construct(private SmallTalkDetector $detector) {}

    public function priority(): int
    {
        return 10;
    }

    public function classify(ClassificationInput $input): ?RoutedIntent
    {
        $match = $this->detector->match($input->question);

        if (! $match->isWholeMessage) {
            return null;
        }

        $intent = match ($match->category) {
            SmallTalkMatch::GREETING => Intent::SmallTalk,
            SmallTalkMatch::ACKNOWLEDGEMENT, SmallTalkMatch::FAREWELL => Intent::Acknowledgement,
            SmallTalkMatch::UNINTELLIGIBLE => Intent::Unintelligible,
            default => null,
        };

        if ($intent === null) {
            return null;
        }

        return new RoutedIntent(
            intent: $intent,
            question: $input->question,
            rawQuestion: $input->rawQuestion,
            strippedSmallTalk: $input->strippedSmallTalk,
            classifier: 'small_talk',
            signals: ['category' => $match->category, 'method' => $match->method],
        );
    }
}
