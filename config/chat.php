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
        // Chunks that reach the LLM for a question that needs BREADTH —
        // a comparison across studies, or broad discovery.
        'final_chunks' => (int) env('CHAT_FINAL_CHUNKS', 8),
        // The cheaper default for an ordinary factual question, which is
        // answered from one or two passages of a single study: the last
        // couple of chunks there are near-duplicates that cost tokens
        // without adding evidence. See ChunkReranker::chunkBudget().
        'final_chunks_focused' => (int) env('CHAT_FINAL_CHUNKS_FOCUSED', 6),
        'max_per_document' => (int) env('CHAT_MAX_PER_DOC', 3),
        'max_documents' => (int) env('CHAT_MAX_DOCS', 5),
        // Applied in PHP after the query — NOT as a SQL WHERE on the
        // distance expression, which would defeat the HNSW index.
        // Deliberately ONE threshold, not one per intent. A looser bar for
        // broad questions was tried and measured worse on both axes: it
        // never improved recall (every broad fixture already answered
        // "grounded, cited" at 0.25), and it pulled out-of-corpus questions
        // like "what studies discuss blockchain?" off the zero-token
        // no-evidence path and into a ~2,000-token LLM call that only
        // declined anyway. Broad DISCOVERY questions are handled by
        // App\Services\Retrieval\CatalogService now, which is what actually
        // fixed them — so the chunk path can afford to stay strict.
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
    | Catalog (discovery / recommendation)
    |--------------------------------------------------------------------------
    |
    | Backs App\Services\Retrieval\CatalogService — the document-level path
    | for "what research is available?", "recommend papers about fish",
    | "how many studies are there?". These questions are answered from
    | stud_file metadata, never from chunk prose: no chunk contains a list
    | of the repository's contents, so chunk search returns nothing above
    | min_similarity and the question dead-ends in the no-evidence reply.
    |
    | 'min_similarity' is deliberately far looser than the retrieval block's:
    | a recommendation is offered as "you might find these relevant", not
    | asserted as a fact needing a citation-grade match. The semantic arm
    | searches ONLY section='metadata' chunks (one per document), so a loose
    | threshold costs breadth, not accuracy.
    |
    */

    'catalog' => [
        'max_documents' => (int) env('CHAT_CATALOG_MAX_DOCS', 10),
        'min_similarity' => (float) env('CHAT_CATALOG_MIN_SIMILARITY', 0.12),
        // Metadata lines are short; a smaller ceiling than the research
        // answer's 700 keeps a list answer from rambling past the list.
        'max_output_tokens' => (int) env('CHAT_CATALOG_MAX_OUTPUT_TOKENS', 450),
        // Characters of each document's abstract included per catalog entry.
        'abstract_chars' => (int) env('CHAT_CATALOG_ABSTRACT_CHARS', 180),
    ],

    /*
    |--------------------------------------------------------------------------
    | Follow-up condensing
    |--------------------------------------------------------------------------
    |
    | App\Services\Chat\QueryCondenser rewrites "which one used CNN?" into a
    | standalone query before it is embedded — without this, retrieval
    | embeds the pronoun and fetches the wrong chunks, which is what made
    | the chatbot feel stateless even though history was already in the
    | prompt. The deterministic path costs nothing; 'llm_fallback' only
    | fires for ordinal/contrastive references ("the second one", "compare
    | them") that a prefix genuinely cannot resolve, and is capped at 40
    | output tokens.
    |
    */

    'condenser' => [
        'llm_fallback' => (bool) env('CHAT_CONDENSER_LLM_FALLBACK', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Context / prompt budget
    |--------------------------------------------------------------------------
    |
    | 'history_messages' is 8 (4 turns) rather than the original 4 (2 turns)
    | because two turns is not a conversation. The token cost of the extra
    | turns is paid for by 'max_history_assistant_chars': a prior assistant
    | answer is resent only as a short reminder of what was said, not in
    | full. Net prompt size is roughly unchanged; conversational reach
    | doubles.
    |
    */

    'context' => [
        'max_evidence_tokens' => (int) env('CHAT_MAX_EVIDENCE_TOKENS', 4000),
        'max_chunk_chars' => (int) env('CHAT_MAX_CHUNK_CHARS', 1600),
        'history_messages' => (int) env('CHAT_HISTORY_MESSAGES', 8),
        'max_history_assistant_chars' => (int) env('CHAT_MAX_HISTORY_ASSISTANT_CHARS', 400),

        // Prior USER turns were uncapped on the reasoning that questions are
        // short. Nothing enforced it: 'max_question_chars' below allows 1000,
        // so four earlier questions could add ~1,100 tokens to every prompt.
        // A prior question only has to be recognisable; the CURRENT question
        // is never truncated.
        'max_history_user_chars' => (int) env('CHAT_MAX_HISTORY_USER_CHARS', 300),

        'max_question_chars' => 1000,

        // A ceiling over the WHOLE assembled prompt — system message, history,
        // active context and evidence together. Until App\Services\Chat\
        // PromptBudget existed, only the evidence set was budgeted
        // ('max_evidence_tokens' above) and everything else rode free on top,
        // so the real prompt had no ceiling at all.
        //
        // Deliberately generous: this is a pathological-case backstop, not the
        // working budget. The per-tier evidence budgets (chat.tiers.*) are what
        // shape a normal turn; if this one is binding regularly, something
        // upstream is wrong and the fix is there, not here.
        'max_prompt_tokens' => (int) env('CHAT_MAX_PROMPT_TOKENS', 6000),

        // Caps on the "Studies discussed earlier" line PromptBuilder emits
        // from ChatConversation::active_context. ConversationContext tracks
        // up to 8 documents and titles run 60-120 chars, so an uncapped line
        // cost ~250 tokens on EVERY turn of a long chat. The 4 most recent
        // resolve "the second one" just as well.
        'max_context_documents' => (int) env('CHAT_MAX_CONTEXT_DOCUMENTS', 4),
        'max_context_title_chars' => (int) env('CHAT_MAX_CONTEXT_TITLE_CHARS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token estimation
    |--------------------------------------------------------------------------
    |
    | Tunables for App\Support\TokenEstimator, the one place that guesses
    | what a string will cost. There is no BPE tokenizer in this project, so
    | every budget in this file is spent against an estimate; the estimator
    | takes max() of a character- and a word-based figure so it errs HIGH.
    |
    | Do not tune these by intuition. Run:
    |
    |     php artisan research:calibrate-token-estimator --days=30
    |
    | which compares these estimates against the prompt_tokens OpenAI
    | actually billed (recorded on chat_messages.retrieval_meta) and reports
    | how often the estimate came in UNDER the real cost. Lowering
    | 'chars_per_token' makes the estimator more conservative.
    |
    */

    'estimator' => [
        'chars_per_token' => (float) env('CHAT_ESTIMATOR_CHARS_PER_TOKEN', 4.0),
        'tokens_per_word' => (float) env('CHAT_ESTIMATOR_TOKENS_PER_WORD', 4 / 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Small talk
    |--------------------------------------------------------------------------
    |
    | App\Services\Chat\SmallTalk\SmallTalkDetector — zero-token handling of
    | greetings, thanks and gibberish. The vocabulary itself is a const in
    | SmallTalkLexicon rather than config: it is reviewed as data, and a typo
    | in an env var should not be able to make "great findings on tilapia"
    | read as chatter.
    |
    | 'fuzzy_enabled' controls typo tolerance ("helo", "hii", "kamusat"),
    | matched with levenshtein() against greeting words only.
    | 'fuzzy_min_length' is load-bearing at 3: below it, "no" -> "yo",
    | "of" -> "ok" and "is" -> "hi" are all one edit away, and a real question
    | would be swallowed. Raise it to disable fuzzy matching on short words
    | entirely; never lower it.
    |
    */

    'smalltalk' => [
        'fuzzy_enabled' => (bool) env('CHAT_SMALLTALK_FUZZY', true),
        'fuzzy_min_length' => (int) env('CHAT_SMALLTALK_FUZZY_MIN_LENGTH', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retrieval tiers (abstract-first)
    |--------------------------------------------------------------------------
    |
    | How much of a document a turn reads. The chat path used to retrieve body
    | chunks unconditionally — 6-8 chunks, up to 1600 chars each, ~4000
    | evidence tokens — even for "what is this study about", which the abstract
    | answers completely.
    |
    | App\Services\Retrieval\RetrievalTierPlanner picks the starting tier
    | deterministically, and EvidenceSufficiencyGate escalates to full text
    | in the SAME turn when the abstract evidence will not do. That escalation
    | is nearly free: the query vector is already cached
    | (cache.query_embedding_ttl), so a second pass costs one Postgres round
    | trip and zero API tokens. Guessing the tier wrong costs latency, not
    | money — which is why the gate is allowed to escalate readily.
    |
    | 'max_output_tokens' is the item to tune first if cost is the concern.
    | gpt-4o-mini bills output at 4x input ($0.60 vs $0.15 per million), so an
    | answer ceiling moves the bill more than any prompt-side trimming does.
    |
    | 'min_similarity' is looser than the full-text threshold
    | (retrieval.min_similarity, 0.25) for the same reason catalog's is: a
    | metadata/abstract chunk is a summary, so it matches a question's wording
    | less tightly than the body passage that actually discusses it.
    |
    */

    'tiers' => [

        'metadata' => [
            'max_evidence_tokens' => (int) env('CHAT_TIER_METADATA_EVIDENCE_TOKENS', 800),
        ],

        'abstract' => [
            'max_evidence_tokens' => (int) env('CHAT_TIER_ABSTRACT_EVIDENCE_TOKENS', 1200),
            'max_output_tokens' => (int) env('CHAT_TIER_ABSTRACT_OUTPUT_TOKENS', 350),
            'max_chunks' => (int) env('CHAT_TIER_ABSTRACT_MAX_CHUNKS', 6),
            'max_per_document' => (int) env('CHAT_TIER_ABSTRACT_MAX_PER_DOC', 2),
            'max_documents' => (int) env('CHAT_TIER_ABSTRACT_MAX_DOCS', 3),
            'min_similarity' => (float) env('CHAT_TIER_ABSTRACT_MIN_SIMILARITY', 0.18),

            // Escalate to full text, in the same turn, when the selected
            // documents produced no abstract-section chunk at all. That happens
            // on short or scanned PDFs where SectionDetector found no abstract
            // heading: the synthetic metadata chunk alone is too thin to answer
            // anything but the most general question from.
            'escalate_when_no_abstract_chunks' => (bool) env('CHAT_TIER_ABSTRACT_ESCALATE_NO_ABSTRACT', true),

            // Escalate when the question's content terms are barely present in
            // the abstract evidence — a proxy for "this question is about
            // something the abstract does not discuss".
            'min_term_coverage' => (float) env('CHAT_TIER_ABSTRACT_MIN_TERM_COVERAGE', 0.40),
        ],

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
