<?php

namespace App\Services\Retrieval;

use App\Models\User;
use App\Models\UserFile;
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
    ) {}

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

        $queryVector = $this->cachedQueryEmbedding($plan['topic']);

        $candidates = (int) config('chat.retrieval.candidates', 24);
        $searchResult = $this->hybridSearch->search($queryVector, $plan['topic'], $user, $plan, $candidates);

        $reranked = $this->reranker->rerank($searchResult['chunks'], $plan['is_comparison'], $plan['is_broad']);

        $nearestDocumentIds = array_values(array_unique(array_column($searchResult['nearest'], 'stud_file_id')));

        return new RetrievalResult(
            chunks: $reranked,
            files: $this->hydrateFiles($reranked),
            filters: $plan,
            isComparison: $plan['is_comparison'],
            relaxedFilters: $searchResult['relaxed_filters'],
            nearestDocumentIds: $nearestDocumentIds,
        );
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

        return Cache::remember(
            $key,
            (int) config('chat.cache.query_embedding_ttl', 86400),
            fn () => $this->embedder->embedBatch([$topic])[0]
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
