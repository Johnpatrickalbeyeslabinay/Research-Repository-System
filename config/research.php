<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document Chunking
    |--------------------------------------------------------------------------
    |
    | Used by App\Services\Research\ChunkingService when preparing a research
    | document's full text for embedding. Values are approximate word counts
    | (PHP has no exact OpenAI-compatible tokenizer); ~4/3 words per token is
    | a common rough estimate for English text.
    |
    */

    'chunking' => [
        'target_words' => env('RESEARCH_CHUNK_TARGET_WORDS', 375), // ~500 tokens
        'overlap_words' => env('RESEARCH_CHUNK_OVERLAP_WORDS', 56), // ~75 tokens
        // Hard ceiling on chunks per document. Nothing else bounds this —
        // an unusually long thesis would otherwise produce 1000+ chunks and
        // embedding calls with no limit. Chunking stops once this is hit;
        // ProcessResearchDocument records the truncation on the record.
        'max_chunks' => env('RESEARCH_MAX_CHUNKS', 400),
    ],

    /*
    |--------------------------------------------------------------------------
    | Indexing
    |--------------------------------------------------------------------------
    |
    | ProcessResearchDocument (chunk + embed a submitted PDF for search)
    | normally dispatches to the real queue (QUEUE_CONNECTION, with retry/
    | backoff on transient OpenAI failures) — this needs `php artisan
    | queue:work` running. Set RESEARCH_INDEX_SYNC=true to fall back to
    | running it inline via ->afterResponse() instead, for environments
    | (e.g. serverless) with no persistent worker process.
    |
    */

    'index_sync' => env('RESEARCH_INDEX_SYNC', false),

    /*
    |--------------------------------------------------------------------------
    | Cron secret
    |--------------------------------------------------------------------------
    |
    | Shared secret for POST /internal/queue/drain, the serverless safety
    | net that runs any job index_sync couldn't finish inline. That route
    | cannot be session-authenticated (a cron has no session), so this IS
    | its authentication: sent as the X-Cron-Secret header and compared with
    | hash_equals(). Left empty, the route 404s — an unconfigured deployment
    | must fail closed, not expose an open queue runner.
    |
    */

    'cron_secret' => env('RESEARCH_CRON_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Embeddings
    |--------------------------------------------------------------------------
    |
    | Bump 'version' to force every document to be re-embedded — e.g. after
    | changing OPENAI_EMBEDDING_MODEL, or after a ChunkingService change that
    | alters chunk boundaries (a version bump, not a code change alone,
    | invalidates old vectors: unchanged text still hashes the same under
    | content_hash, so ProcessResearchDocument's embedding-reuse lookup
    | would otherwise keep serving stale vectors from before the change).
    | `php artisan research:index-backfill` finds every stud_file whose
    | stored research_chunks rows don't match this version/model and
    | re-indexes it.
    |
    */

    'embedding' => [
        'version' => env('RESEARCH_EMBEDDING_VERSION', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    |
    | Single source of truth for the research PDF size limit — read by the
    | client-side dropzone (via a data attribute), both FormRequests, and
    | analyzePdf(), so the three never drift out of sync with each other.
    | Note this only constrains what the *app* accepts; php.ini's
    | upload_max_filesize/post_max_size must independently allow at least
    | this much, or the file never reaches Laravel at all.
    |
    */

    'max_upload_mb' => env('RESEARCH_MAX_UPLOAD_MB', 10),

    /*
    |--------------------------------------------------------------------------
    | File Storage
    |--------------------------------------------------------------------------
    |
    | Which filesystems.php disk research PDFs are stored on. Defaults to
    | 'local' for dev convenience (no cloud credentials needed to test file
    | upload/view/download locally). In any real deployment this MUST be a
    | disk backed by persistent storage — this app deploys to Vercel
    | (see vercel.json) as serverless PHP functions, whose filesystem is
    | read-only outside /tmp and doesn't persist between requests or across
    | function instances, so 'local' silently loses every uploaded file in
    | production. Set RESEARCH_FILES_DISK=s3 with the 's3' disk's env vars
    | pointed at Supabase Storage's S3-compatible endpoint (Project Settings
    | > Storage > S3 Connection) to use the same Supabase project already
    | hosting the database.
    |
    */

    'storage_disk' => env('RESEARCH_FILES_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Scanned PDF detection
    |--------------------------------------------------------------------------
    |
    | App\Services\Pdf\ScannedPdfDetector: the share of pages that are
    | images with (almost) no text. At or above 'scanned_ratio' the upload is
    | rejected; at or above 'partial_ratio' it is accepted with a warning.
    |
    */

    'scan_detection' => [
        'scanned_ratio' => (float) env('RESEARCH_SCANNED_RATIO', 0.6),
        'partial_ratio' => (float) env('RESEARCH_PARTIAL_SCAN_RATIO', 0.2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Submission AI daily quota
    |--------------------------------------------------------------------------
    |
    | Per-student, per-day cap on AI calls made from the submission form
    | (abstract generation + keyword/SDG suggestions) — see
    | App\Support\SubmissionAiQuota. Only calls that actually reach OpenAI
    | count; a cached result (same PDF, same title/abstract) is free.
    |
    */

    'ai' => [
        'per_day' => (int) env('RESEARCH_AI_PER_DAY', 10),
    ],

];
