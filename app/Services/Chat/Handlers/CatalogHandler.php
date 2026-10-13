<?php

namespace App\Services\Chat\Handlers;

use App\Services\Chat\IntentDetector;
use App\Services\Chat\PromptBuilder;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Turn\ChatTurn;
use App\Services\Chat\Turn\TurnHandler;
use App\Services\Chat\Turn\TurnOutcome;
use App\Services\Retrieval\CatalogService;
use App\Services\Retrieval\QueryPlanner;
use App\Services\Retrieval\RetrievalService;
use App\Services\Retrieval\Tier;

/**
 * Document-level questions: "what research is available?", "recommend papers
 * about fish".
 *
 * These ask what EXISTS, and the answer lives in stud_file metadata — no chunk
 * of any PDF contains a list of the repository's contents, so running them
 * through chunk retrieval matched nothing above min_similarity and always
 * produced the no-evidence reply. Verified live against a fully indexed corpus.
 */
final class CatalogHandler implements TurnHandler
{
    public function __construct(
        private QueryPlanner $planner,
        private CatalogService $catalog,
        private RetrievalService $retrieval,
        private IntentDetector $intents,
        private PromptBuilder $prompts,
    ) {}

    public function handles(): array
    {
        return [Intent::Catalog];
    }

    public function handle(ChatTurn $turn): TurnOutcome
    {
        $question = $turn->question();
        $plan = $this->planner->plan($question, $turn->conversation->active_context, null);

        // A bare inventory question ("list repository available") has no real
        // topic — embedding it produces a meaningless vector, so skip the
        // semantic arm and the embedding call with it. Otherwise deferred:
        // CatalogService only embeds once it knows there is something to rank.
        $queryVector = $this->intents->isBareInventory($question)
            ? null
            : fn () => $this->retrieval->topicVector($plan['topic']);

        $catalog = $this->catalog->browse($turn->user, $plan, $queryVector);

        if ($catalog->isEmpty()) {
            // Nothing to describe, so nothing worth asking the model to
            // describe. The canned reply is kept OUT of history: resent on
            // later turns, "no research matched" nudges the model toward
            // declining again.
            return TurnOutcome::canned(
                content: $this->catalog->emptyMessage($plan, $catalog->unindexed),
                retrievalMeta: $turn->routed->toMeta() + [
                    'skipped' => 'catalog_empty',
                    'filters' => $plan,
                ],
                catalog: $catalog,
                // Recorded as a zero-cost 'catalog' operation. The turn spent
                // nothing, but it did consult the database, and an operation
                // that vanishes from cost reports is one nobody notices
                // breaking.
                usageOperation: 'catalog',
            );
        }

        return TurnOutcome::answered(
            prompt: $this->prompts->buildCatalog($turn->conversation, $catalog, $question, $turn->userMessage->id),
            usageOperation: 'catalog',
            retrievalMeta: $turn->routed->toMeta() + [
                'documents_shown' => $catalog->documents->count(),
                'documents_total' => $catalog->total,
                'semantic' => $catalog->semantic,
                'filters' => $plan,
                'prompt_version' => PromptBuilder::promptVersion(),
            ],
            catalog: $catalog,
            chunksRetrieved: $catalog->documents->count(),
            maxOutputTokens: Tier::Metadata->maxOutputTokens(),
        );
    }
}
