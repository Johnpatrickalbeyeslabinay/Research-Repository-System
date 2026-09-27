<?php

namespace App\Services\Chat\Routing;

/**
 * One classification rule.
 *
 * Contract, enforced by tests rather than by hope:
 *
 *  - priority() is an explicit integer, and IntentRouter sorts by it. It is
 *    NOT registration order. Conflating the two is exactly how a precedence
 *    bug ships silently: someone reorders a service-provider binding for
 *    unrelated reasons and "recommend a good restaurant" starts returning
 *    fisheries papers again.
 *
 *  - classify() must be PURE. No database query, no HTTP call, no clock, no
 *    randomness. IntentRouterTest asserts an empty query log and
 *    Http::assertNothingSent() across the whole question corpus, because the
 *    entire value of routing before retrieval is that it costs nothing —
 *    a classifier that queries is a classifier that can be slow or fail on a
 *    turn that was supposed to be free.
 */
interface TurnClassifier
{
    /** Lower runs first. */
    public function priority(): int;

    /** Return null to pass the turn to the next classifier. */
    public function classify(ClassificationInput $input): ?RoutedIntent;
}
