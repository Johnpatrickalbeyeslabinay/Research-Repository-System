<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | The chatbot's final answer generation. Kept here — not hardcoded in
    | App\Services\Chat\ChatAnswerService — for the same reason
    | config/services.php's openai block exists: swapping models shouldn't
    | mean touching business logic. Query/document embeddings reuse
    | services.openai.embedding_model (they must match — see
    | App\Services\Retrieval\RetrievalService); there is no separate
    | "planner" model — App\Services\Retrieval\QueryPlanner is deterministic
    | regex/lookup, not an AI call (see its class docblock for why).
    |
    */

    'models' => [
        'answer' => env('OPENAI_CHAT_MODEL', 'gpt-4o-mini'),
        'max_output_tokens' => (int) env('OPENAI_CHAT_MAX_OUTPUT_TOKENS', 700),
        'timeout' => (int) env('OPENAI_CHAT_TIMEOUT', 45),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retrieval
    |--------------------------------------------------------------------------
    |
    | Used by App\Services\Retrieval\VectorSearchService (candidates,
    | min_similarity, hnsw_ef_search) and App\Services\Retrieval\ChunkReranker
    | (everything else) to keep the evidence handed to the LLM small and
    | relevant — see the "never send the whole corpus" rule in the project's
    | RAG plan. 'references' never needs a weight here: ChunkingService
    | excludes that section entirely at chunk time, so it can never reach
    | the reranker in the first place.
    |
    */

    'retrieval' => [
        // Rows pulled from pgvector before reranking narrows them down.
        'candidates' => (int) env('CHAT_CANDIDATES', 24),
        // Chunks that actually reach the LLM after reranking.
        'final_chunks' => (int) env('CHAT_FINAL_CHUNKS', 8),
        'max_per_document' => (int) env('CHAT_MAX_PER_DOC', 3),
        'max_documents' => (int) env('CHAT_MAX_DOCS', 5),
        // Applied in PHP after the query — NOT as a SQL WHERE on the
        // distance expression, which would defeat the HNSW index.
        'min_similarity' => (float) env('CHAT_MIN_SIMILARITY', 0.25),
        // Must only ever be set via `SET LOCAL` inside a DB::transaction() —
        // the app connects through PgBouncer in transaction pooling mode
        // (DB_PORT=6543), where a bare `SET` is unsafe.
        'hnsw_ef_search' => (int) env('CHAT_HNSW_EF_SEARCH', 60),
        'section_weights' => [
            'metadata' => 1.15,
            'abstract' => 1.10,
            'results' => 1.10,
            'conclusion' => 1.10,
            'discussion' => 1.05,
            'methodology' => 1.00,
            'introduction' => 0.95,
        ],
        // Token-Jaccard overlap above which a candidate chunk is dropped as
        // a near-duplicate of one already selected (MMR-style diversity).
        'mmr_overlap_threshold' => 0.80,
        // A filtered query (year/SDG/author/type) can starve HNSW's
        // post-filtering — over-fetch this multiple of 'candidates' when
        // filters are present, and fall back to an unfiltered search once
        // if too few rows survive. See VectorSearchService.
        'filtered_overfetch_multiplier' => 3,
        'filtered_min_results' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Context / prompt budget
    |--------------------------------------------------------------------------
    */

    'context' => [
        'max_evidence_tokens' => (int) env('CHAT_MAX_EVIDENCE_TOKENS', 4000),
        'max_chunk_chars' => (int) env('CHAT_MAX_CHUNK_CHARS', 1600),
        'history_messages' => (int) env('CHAT_HISTORY_MESSAGES', 4),
        'max_question_chars' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate limits
    |--------------------------------------------------------------------------
    |
    | 'per_minute' backs the route's throttle:; 'per_day' backs
    | App\Http\Middleware\ChatDailyQuota, counted against ai_usage_logs.
    |
    */

    'limits' => [
        'per_minute' => (int) env('CHAT_RATE_PER_MINUTE', 6),
        'per_day' => (int) env('CHAT_RATE_PER_DAY', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Query embeddings are cached (they cost nothing to reuse across users
    | asking similar things). Answers are deliberately NEVER cached — two
    | users asking the identical question can have different permission
    | sets and therefore legitimately deserve different answers; caching
    | the embedding, not the answer, is what keeps that correct.
    |
    */

    'cache' => [
        'query_embedding_ttl' => (int) env('CHAT_QUERY_EMBEDDING_TTL', 86400),
    ],

];
