<?php

namespace App\Services\Chat\Routing\Classifiers;

use App\Services\Chat\PlatformQuestionDetector;
use App\Services\Chat\Routing\ClassificationInput;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Routing\RoutedIntent;
use App\Services\Chat\Routing\TurnClassifier;

/**
 * Questions about the application itself. A thin adapter over the existing
 * PlatformQuestionDetector, which is NOT modified — its six patterns and its
 * deliberate refusal to claim a bare "repository" are load-bearing and already
 * covered by tests.
 *
 * Runs after Identity so "what can you do here" is answered from a constant
 * rather than spending a call on PLATFORM_INFO.
 */
final class PlatformClassifier implements TurnClassifier
{
    public function __construct(private PlatformQuestionDetector $detector) {}

    public function priority(): int
    {
        return 30;
    }

    public function classify(ClassificationInput $input): ?RoutedIntent
    {
        if (! $this->detector->isPlatformQuestion($input->question)) {
            return null;
        }

        return new RoutedIntent(
            intent: Intent::Platform,
            question: $input->question,
            rawQuestion: $input->rawQuestion,
            strippedSmallTalk: $input->strippedSmallTalk,
            classifier: 'platform',
        );
    }
}
