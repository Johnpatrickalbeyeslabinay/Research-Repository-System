<?php

namespace App\Services\Retrieval;

use App\Models\User;
use App\Models\UserFile;
use App\Services\Chat\QueryCondenser;
use App\Services\Research\EmbeddingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Orchestrates the full retrieval pipeline: QueryPlanner -> query embedding
 * (cached) -> HybridSearchService (vector + lexical, RRF-fused) ->
 * ChunkReranker -> hydrated UserFile models. This is the one entry point
 * App\Http\Controllers\ChatController calls; it has no knowledge of the
 * LLM/prompt/answer side of the chatbot, matching the project's "the
 * vector database finds information, the LLM explains it" separation of
 * responsibilities.
 */
class RetrievalService
{
    public function __construct(
        private QueryPlanner $planner,
        private EmbeddingService $embedder,
        private HybridSearchService $hybridSearch,
        private ChunkReranker $reranker,
        private QueryCondenser $condenser,
        private RetrievalTierPlanner $tiers,
        private EvidenceSufficiencyGate $gate,
    ) {}

    /** Set by cachedQueryEmbedding(); see lastEmbeddingWasCached(). */
    private bool $lastEmbeddingWasCached = true;

    private int $lastEmbeddingPromptTokens = 0;

    /**
     * @param  ?array{topic?: string, filters?: array, research_ids?: int[]}  $activeContext
     *         ChatConversation::active_context from the previous turn.
     * @param  ?int  $pinnedStudFileId  set for a single-research chat.
     */
    public function retrieve(
        string $question,
        ?User $user,
        ?array $activeContext = null,
        ?int $pinnedStudFileId = null
    ): RetrievalResult {
        $plan = $this->planner->plan($question, $activeContext, $pinnedStudFileId);

        // Resolve the follow-up BEFORE embedding. A pinned single-research
        // chat is already scoped to one document, so there is nothing
        // ambiguous to resolve and nothing to gain from rewriting.
        $condensed = $pinnedStudFileId === null
            ? $this->condenser->condense($plan['topic'], $activeContext)
            : ['topic' => $plan['topic'], 'is_follow_up' => false, 'method' => 'none'];

        // Only the SEARCH text changes. $plan['topic'] stays as the planner
        // derived it from the user's literal words, and the answer prompt
        // still receives the real question — a rewritten query must never
        // become a rewritten question.
        $searchText = $condensed['topic'];
        $plan['is_follow_up'] = $condensed['is_follow_up'];
        $plan['condense_method'] = $condensed['method'];

        $embeddingStart = microtime(true);
        $queryVector = $this->cachedQueryEmbedding($searchText);
        $embeddingLatencyMs = (int) round((microtime(true) - $embeddingStart) * 1000);

        // How deep to read. Abstract by default; see RetrievalTierPlanner.
        $decision = $this->tiers->plan($question, $plan, $activeContext, $pinnedStudFileId !== null);

        [$searchResult, $reranked, $decision] = $this->searchAtTier(
            $decision, $question, $searchText, $queryVector, $user, $plan,
        );

        $nearestDocumentIds = array_values(array_unique(array_column($searchResult['nearest'], 'stud_file_id')));

        return new RetrievalResult(
            chunks: $reranked,
            files: $this->hydrateFiles($reranked),
            filters: $plan,
            isComparison: $plan['is_comparison'],
            relaxedFilters: $searchResult['relaxed_filters'],
            nearestDocumentIds: $nearestDocumentIds,
            embeddingWasCached: $this->lastEmbeddingWasCached,
            embeddingPromptTokens: $this->lastEmbeddingPromptTokens,
            embeddingLatencyMs: $embeddingLatencyMs,
            tier: $decision,
        );
    }

    /**
     * Searches at the planned tier, escalating once if the evidence will not do.
     *
     * At most ONE escalation, and it happens before any answer call, so a turn
     * still makes exactly one LLM call. The retry is cheap because
     * $queryVector is already computed and cached: it costs one more Postgres
     * round trip and no API tokens, which is what makes defaulting to the
     * cheaper tier safe.
     *
     * @return array{0: array, 1: array, 2: TierDecision}
     */
    private function searchAtTier(
        TierDecision $decision,
        string $question,
        string $searchText,
        array $queryVector,
        ?User $user,
        array $plan,
    ): array {
        $candidates = (int) config('chat.retrieval.candidates', 24);

        $run = function (TierDecision $d) use ($queryVector, $searchText, $user, $plan, $candidates) {
            $searchResult = $this->hybridSearch->search(
                $queryVector,
                $searchText,
                $user,
                $plan,
                $candidates,
                $d->tier->sections(),
                $d->tier === Tier::Abstract
                    ? (float) config('chat.tiers.abstract.min_similarity', 0.18)
                    : null,
            );

            $reranked = $this->reranker->rerank(
                $this->dropDuplicatePdfChunks($searchResult['chunks']),
                $plan['is_comparison'],
                $plan['is_broad'],
                $d->tier,
            );

            return [$searchResult, $reranked];
        };

        [$searchResult, $reranked] = $run($decision);

        if ($decision->tier === Tier::Abstract) {
            $escalation = $this->gate->shouldEscalate($question, $reranked);

            if ($escalation !== null) {
                $decision = $escalation;
                [$searchResult, $reranked] = $run($decision);
            }
        }

        return [$searchResult, $reranked, $decision];
    }

    /**
     * Collapses chunks belonging to duplicate copies of the same PDF, keeping
     * the best-scoring copy.
     *
     * CatalogService::dedupeByPdf() and ChatController::suggestedResearchCards()
     * both already do this for their own shapes; the chunk path did not. The
     * MMR jaccard check in ChunkReranker catches byte-identical chunk TEXT, so
     * the prompt was never literally repetitive — but ChunkReranker's
     * max_documents cap counts stud_file ROWS, so three duplicate submissions
     * of one paper consumed three of the five document slots. On a corpus that
     * is largely duplicate test uploads of the same file, that is the
     * difference between a comparison answer covering five distinct studies and
     * one covering three.
     *
     * Runs before reranking so the caps operate on distinct studies. A row with
     * no pdf_hash (a legacy record predating the column) is never deduped
     * against anything — a missing hash carries no dedupe information — which
     * matches the rule the other two call sites use.
     *
     * @param  array<int, array{stud_file_id:int}>  $chunks
     * @return array<int, array<string, mixed>>
     */
    private function dropDuplicatePdfChunks(array $chunks): array
    {
        if (empty($chunks)) {
            return $chunks;
        }

        $ids = array_values(array_unique(array_column($chunks, 'stud_file_id')));

        if (count($ids) < 2) {
            return $chunks;
        }

        $hashes = UserFile::whereIn('id', $ids)
            ->whereNotNull('pdf_hash')
            ->pluck('pdf_hash', 'id');

        if ($hashes->isEmpty()) {
            return $chunks;
        }

        // $chunks arrives ordered by fused relevance, so the first document
        // encountered for a hash is the best-scoring copy of that PDF.
        $keptIdForHash = [];
        $rejectedIds = [];

        foreach ($ids as $id) {
            $hash = $hashes[$id] ?? null;

            if ($hash === null) {
                continue;
            }

            if (isset($keptIdForHash[$hash])) {
                $rejectedIds[$id] = true;

                continue;
            }

            $keptIdForHash[$hash] = $id;
        }

        if (empty($rejectedIds)) {
            return $chunks;
        }

        return array_values(array_filter(
            $chunks,
            fn (array $chunk) => ! isset($rejectedIds[$chunk['stud_file_id']]),
        ));
    }

    /**
     * The query embedding for a topic, sharing this service's cache.
     *
     * Exposed for App\Services\Retrieval\CatalogService, which needs a
     * query vector but not the rest of the chunk pipeline: a catalog
     * question ("recommend papers about fish") and a research question
     * about the same topic should hit the same cached embedding rather
     * than paying for two identical API calls.
     *
     * @return float[]
     */
    public function topicVector(string $topic): array
    {
        return $this->cachedQueryEmbedding($topic);
    }

    /**
     * Whether the most recent embedding lookup was served from cache, and
     * what it cost if it wasn't.
     *
     * These exist because ChatController used to write a 'query_embedding'
     * row to ai_usage_logs on EVERY research turn, with prompt_tokens null
     * and latency_ms set to the whole retrieval duration (search + rerank +
     * hydrate). That row recorded neither real tokens nor a real embedding
     * latency, and it claimed an API call had happened on the ~24 hours of
     * cache hits where none had. A cost report built on it would be
     * fiction, so it had to be fixed before anything in this plan could be
     * measured.
     *
     * Read them immediately after the call that produced them — they
     * describe the last lookup on this instance, not a running total.
     */
    public function lastEmbeddingWasCached(): bool
    {
        return $this->lastEmbeddingWasCached;
    }

    public function lastEmbeddingPromptTokens(): int
    {
        return $this->lastEmbeddingPromptTokens;
    }

    /**
     * Cached by a hash of the normalized topic + embedding model, not the
     * raw question — repeated or similarly-phrased questions (across any
     * user; permissions are enforced later, in VectorSearchService, not
     * here) cost nothing to re-embed. This is the one cache in the whole
     * pipeline that's safe to share across users: an embedding carries no
     * information about who asked or what they're allowed to see.
     *
     * @return float[]
     */
    private function cachedQueryEmbedding(string $topic): array
    {
        $normalized = mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $topic)));
        $key = 'qemb:v1:'.hash('sha256', $normalized.'|'.config('services.openai.embedding_model'));

        // Assume a hit; the closure below only runs on a miss and is what
        // flips this. Set before Cache::remember() so a thrown embedding
        // call can't leave a stale value from a previous lookup behind.
        $this->lastEmbeddingWasCached = true;
        $this->lastEmbeddingPromptTokens = 0;

        return Cache::remember(
            $key,
            (int) config('chat.cache.query_embedding_ttl', 86400),
            function () use ($topic) {
                $result = $this->embedder->embedBatch([$topic]);

                $this->lastEmbeddingWasCached = false;
                $this->lastEmbeddingPromptTokens = $result['prompt_tokens'];

                return $result['embeddings'][0];
            }
        );
    }

    /**
     * @param  array<int, array{stud_file_id: int}>  $chunks
     * @return Collection<int, UserFile>
     */
    private function hydrateFiles(array $chunks): Collection
    {
        $ids = array_values(array_unique(array_column($chunks, 'stud_file_id')));

        if (empty($ids)) {
            return collect();
        }

        return UserFile::with(['authors', 'keywords', 'sdgs'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }
}
