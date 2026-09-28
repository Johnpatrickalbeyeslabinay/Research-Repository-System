<?php

namespace App\Services\Chat\Handlers;

use App\Services\Chat\ExternalCitationBuilder;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Turn\ChatTurn;
use App\Services\Chat\Turn\TurnHandler;
use App\Services\Chat\Turn\TurnOutcome;
use App\Services\External\ExternalLiteratureService;
use App\Services\Retrieval\QueryPlanner;

/**
 * An explicit request for research beyond this repository.
 *
 * Zero OpenAI tokens: the works are rendered as canned text plus citation cards
 * rather than passed through the model. There is nothing a model can add to a
 * list of titles it must not characterise anyway (system prompt rule 15), and
 * because the answer is assembled in PHP the claim boundary is GUARANTEED
 * rather than instructed.
 *
 * The topic sent outward is the USER'S OWN WORDS, never text pulled from
 * retrieved chunks — a condensed topic could otherwise carry a private paper's
 * title off-site. The classifier also refuses to fire in a pinned conversation
 * for the same reason.
 */
final class ExternalLiteratureHandler implements TurnHandler
{
    public function __construct(
        private QueryPlanner $planner,
        private ExternalLiteratureService $external,
        private ExternalCitationBuilder $citations,
    ) {}

    public function handles(): array
    {
        return [Intent::ExternalLiterature];
    }

    public function handle(ChatTurn $turn): TurnOutcome
    {
        $plan = $this->planner->plan($turn->question(), $turn->conversation->active_context, null);

        $result = $this->external->search(
            $turn->question(),
            ['year_from' => $plan['year_from'] ?? null, 'year_to' => $plan['year_to'] ?? null],
            $turn->user?->id,
        );

        [$text, $sources] = $this->citations->build($result);

        return TurnOutcome::canned(
            content: $text,
            retrievalMeta: $turn->routed->toMeta() + [
                'external_provider' => $result->provider,
                'external_works' => count($result->works),
                'external_degraded' => $result->degraded,
            ],
            externalSources: $sources,
            excludeFromHistory: false,
        );
    }
}
