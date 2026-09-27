<?php

namespace App\Services\Chat\Routing;

use App\Services\Chat\SmallTalk\SmallTalkDetector;

/**
 * The one place that decides what a chat message is.
 *
 * Replaces a five-branch if/else inside ChatController::message(), where the
 * intent taxonomy existed only as the ORDER of those branches — invisible,
 * untestable, and needing another edit to that chain for every new behaviour.
 *
 * The router owns the two things no individual classifier can:
 *
 *  1. Normalization and greeting stripping, so every classifier sees the same
 *     cleaned text. Without this, each would have to re-handle "hi, ..." and
 *     they would inevitably disagree about it.
 *
 *  2. The terminal fallback. ResearchQa is deliberately NOT a registered
 *     classifier: "anything unclaimed is a research question" is the
 *     pipeline's foundational assumption, and leaving it as the router's
 *     documented default means it cannot be accidentally reordered or
 *     deleted along with a classifier.
 */
class IntentRouter
{
    /** @var TurnClassifier[] sorted by priority() ascending */
    private array $classifiers;

    /**
     * @param  iterable<TurnClassifier>  $classifiers
     */
    public function __construct(iterable $classifiers, private SmallTalkDetector $smallTalk)
    {
        $sorted = is_array($classifiers) ? $classifiers : iterator_to_array($classifiers);

        // Sorted HERE, by explicit priority, rather than trusting the order
        // they were registered in. A service-provider binding reordered for
        // unrelated reasons must not be able to change routing behaviour.
        usort($sorted, fn (TurnClassifier $a, TurnClassifier $b) => $a->priority() <=> $b->priority());

        $this->classifiers = $sorted;
    }

    public function route(
        string $message,
        bool $isPinnedConversation = false,
        ?array $activeContext = null,
    ): RoutedIntent {
        $raw = trim($message);

        // Strip a leading greeting BEFORE classifying, so "hi, what research
        // is there about tilapia?" is classified on the question. The match
        // itself decides whether stripping is safe: "hi-tech aquaculture" has
        // no greeting boundary, so nothing is removed.
        $match = $this->smallTalk->match($raw);

        $question = $match->hasStrippedPrefix() ? $match->residual : $raw;
        $stripped = $match->hasStrippedPrefix() ? $raw : null;

        $input = new ClassificationInput(
            question: $question,
            rawQuestion: $raw,
            strippedSmallTalk: $stripped,
            isPinnedConversation: $isPinnedConversation,
            activeContext: $activeContext,
        );

        foreach ($this->classifiers as $classifier) {
            if ($routed = $classifier->classify($input)) {
                return $routed;
            }
        }

        return new RoutedIntent(
            intent: Intent::ResearchQa,
            question: $question,
            rawQuestion: $raw,
            strippedSmallTalk: $stripped,
            classifier: 'default',
        );
    }

    /**
     * The resolved order, for the test that asserts precedence is what we
     * think it is rather than what registration happened to produce.
     *
     * @return array<int, array{priority: int, class: string}>
     */
    public function resolvedOrder(): array
    {
        return array_map(fn (TurnClassifier $c) => [
            'priority' => $c->priority(),
            'class' => $c::class,
        ], $this->classifiers);
    }
}
