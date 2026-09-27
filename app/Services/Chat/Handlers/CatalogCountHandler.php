<?php

namespace App\Services\Chat\Handlers;

use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Turn\ChatTurn;
use App\Services\Chat\Turn\TurnHandler;
use App\Services\Chat\Turn\TurnOutcome;
use App\Services\Retrieval\CatalogService;
use App\Services\Retrieval\QueryPlanner;

/**
 * "How many studies are in the repository?"
 *
 * The entire answer is CatalogResult::$total — a number PHP already holds — yet
 * this used to spend a ~600-token catalog prompt plus a 450-token output
 * ceiling to restate it in a sentence. Output bills at 4x input on gpt-4o-mini,
 * and this is one of the most common questions a repository gets.
 *
 * Still goes through CatalogService rather than counting rows directly, because
 * $total must respect ResearchAccess: the count a student sees is not the count
 * an admin sees, and reporting the raw table size would leak how much private
 * research exists.
 */
final class CatalogCountHandler implements TurnHandler
{
    public function __construct(
        private QueryPlanner $planner,
        private CatalogService $catalog,
    ) {}

    public function handles(): array
    {
        return [Intent::CatalogCount];
    }

    public function handle(ChatTurn $turn): TurnOutcome
    {
        $plan = $this->planner->plan($turn->question(), $turn->conversation->active_context, null);

        // Bare inventory: no topic worth embedding, so no embedding call.
        $catalog = $this->catalog->browse($turn->user, $plan, null);

        return TurnOutcome::canned(
            content: $this->reply($catalog->total),
            retrievalMeta: $turn->routed->toMeta() + ['total' => $catalog->total],
            suggestedResearch: $catalog->documents->pluck('id')->take(3)->all(),
            excludeFromHistory: false,
            catalog: $catalog,
        );
    }

    private function reply(int $total): string
    {
        if ($total === 0) {
            return 'There is no research in the repository you can access yet. '
                .'Once submissions are approved and made public they will show up here.';
        }

        if ($total === 1) {
            return 'There is 1 research document you can access. Ask me what it is about, '
                .'or search by topic, author, year or SDG.';
        }

        return "There are {$total} research documents you can access. "
            .'Ask me about a topic, author, year or SDG and I will narrow it down.';
    }
}
