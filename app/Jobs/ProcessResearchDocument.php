<?php

namespace App\Jobs;

use App\Exceptions\PdfProcessingException;
use App\Models\AiUsageLog;
use App\Models\ResearchChunk;
use App\Models\UserFile;
use App\Services\Pdf\PdfTextExtractor;
use App\Services\Pdf\SectionDetector;
use App\Services\Research\ChunkingService;
use App\Services\Research\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Makes a submitted research document searchable by chunking its extracted
 * text and embedding each chunk into `research_chunks` (pgvector). This is
 * the document-ingestion pipeline — separate from the metadata AI call
 * (MetadataAnalysisService): it re-runs the same deterministic PDF
 * extraction/section-detection/chunking used elsewhere, then makes exactly
 * one kind of AI call (embeddings), never a generative one.
 *
 * Dispatch mode is config('research.index_sync'): by default this goes
 * through the real queue (retried via $tries/backoff on transient OpenAI
 * failures), which needs `php artisan queue:work` running. Set
 * RESEARCH_INDEX_SYNC=true to dispatch ->afterResponse() instead — runs
 * inline in the request process, for environments (e.g. serverless) with
 * no persistent worker. See StudentController::store()/update().
 */
class ProcessResearchDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Only meaningful when actually dispatched to a queue worker — a job
    // run via ->afterResponse() executes once, inline, with no retry loop.
    public int $tries = 3;

    public function __construct(public int $studFileId) {}

    /**
     * Exponential backoff between retries on the real queue: 10s, 30s, 60s.
     * Gives a transient OpenAI rate-limit or timeout room to clear before
     * trying again, rather than hammering it three times back-to-back.
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(
        PdfTextExtractor $extractor,
        SectionDetector $sectionDetector,
        ChunkingService $chunker,
        EmbeddingService $embedder
    ): void {
        $studFile = UserFile::find($this->studFileId);

        if (! $studFile) {
            return; // deleted before this ran
        }

        $studFile->search_index_status = 'processing';
        $studFile->search_index_error = null;
        $studFile->save();

        try {
            $disk = config('research.storage_disk', 'local');
            [$absolutePath, $tempFile] = $this->resolveLocalPath($disk, $studFile->file_path);

            try {
                // Cached by pdf_hash (see PdfTextExtractor::extractCached() /
                // SectionDetector::detectCached()) — this document was
                // typically already parsed once during analyzePdf()/store(),
                // so this is usually a cache hit rather than a second full
                // smalot/pdfparser pass.
                $extracted = $extractor->extractCached($studFile->pdf_hash, $absolutePath);
                $detected = $sectionDetector->detectCached($studFile->pdf_hash, $extracted['pages'], $extracted['full_text']);
            } finally {
                if ($tempFile !== null) {
                    @unlink($tempFile);
                }
            }

            $chunkResult = $chunker->chunk(
                $extracted['pages'],
                $extracted['full_text'],
                $detected['sections'],
                $this->documentSummary($studFile),
                // Used only when no ABSTRACT heading was detected in the PDF.
                // Prefers the student's typed abstract over the parsed one, the
                // same precedence documentSummary() uses.
                $studFile->desc ?: $studFile->extracted_abstract,
            );
            $chunks = $chunkResult['chunks'];

            $embedded = empty($chunks)
                ? ['rows' => [], 'prompt_tokens' => 0, 'embedded_chunks' => 0]
                : $this->resolveEmbeddings($chunks, $embedder);

            $rows = $embedded['rows'];

            // Indexing is the single largest consumer of the OpenAI key, and
            // until this line it wrote nothing to ai_usage_logs at all —
            // even though that table's own migration comment lists
            // 'doc_embedding' as an expected operation. Document embedding
            // spend was therefore invisible, which makes any claim about
            // reducing token usage unverifiable.
            //
            // Logged only when tokens were actually spent: content-hash
            // reuse (see resolveEmbeddings) means a re-index of an unchanged
            // PDF makes no API call, and a zero-token row would misreport
            // that as spend.
            if ($embedded['prompt_tokens'] > 0) {
                AiUsageLog::record([
                    'user_id' => $studFile->user_id,
                    'request_id' => (string) Str::uuid(),
                    'operation' => 'doc_embedding',
                    'model' => $this->embeddingModel(),
                    'prompt_tokens' => $embedded['prompt_tokens'],
                    'total_tokens' => $embedded['prompt_tokens'],
                    'chunks_retrieved' => $embedded['embedded_chunks'],
                    'success' => true,
                ]);
            }

            // Embeddings are fully resolved above BEFORE anything is
            // deleted. Previously the old index was deleted first and only
            // then re-embedded — an embedding failure (network, rate
            // limit, bad key) left the document with zero chunks instead
            // of its last-good index. Delete + insert now happen together,
            // atomically, only once we have something to replace it with.
            DB::transaction(function () use ($studFile, $rows) {
                ResearchChunk::where('stud_file_id', $studFile->id)->delete();
                $this->insertChunksBatch($studFile->id, $rows);
            });

            $studFile->search_index_status = 'ready';
            $studFile->search_indexed_at = now();
            $studFile->search_index_error = $chunkResult['truncated']
                ? 'Indexed successfully, but this document is unusually long — only the first '
                    .count($chunks).' chunks were indexed, so search may not cover the full text.'
                : null;
            $studFile->save();
        } catch (\Throwable $e) {
            Log::error('ProcessResearchDocument failed', [
                'stud_file_id' => $studFile->id,
                'error' => $e->getMessage(),
            ]);

            // PDF-content problems (corrupted/encrypted/unreadable) won't
            // resolve themselves — the student needs to replace the file.
            // Anything else (bad/expired API key, rate limit, timeout,
            // transient network failure) is transient: a later run against
            // the same file can succeed with no changes needed. Tagging
            // this distinction in the stored message is what lets a
            // dead OpenAI key read as "will retry" rather than "this
            // document is broken."
            $retryable = ! $e instanceof PdfProcessingException;
            $studFile->search_index_status = 'failed';
            $studFile->search_index_error = ($retryable ? '[retryable] ' : '[permanent] ').$e->getMessage();
            $studFile->save();
        }
    }

    /**
     * A single synthetic chunk (title/authors/year/type/keywords/SDGs/
     * abstract) that ChunkingService prepends as chunk_index=0,
     * section='metadata'. Broad discovery questions ("what research exists
     * about X?", "which studies support SDG 4?") match this far better
     * than body prose — a body chunk from the middle of Methodology has no
     * reason to mention the paper's title or its SDGs at all.
     *
     * Deliberately built here, not inside ChunkingService — that service
     * stays a pure text-processing class with no database/relationship
     * knowledge (see its class docblock), while UserFile::researcherNames()
     * and the sdgs()/keywords() relations already exist on the model this
     * job already loaded.
     */
    private function documentSummary(UserFile $studFile): string
    {
        $studFile->loadMissing(['authors', 'keywords', 'sdgs']);

        $parts = [
            $studFile->title.'.',
        ];

        if ($authors = $studFile->researcherNames()) {
            $parts[] = 'Authors: '.implode(', ', $authors).'.';
        }

        if ($studFile->publication_year) {
            $parts[] = "Year: {$studFile->publication_year}.";
        }

        if ($studFile->research_type) {
            $parts[] = "Type: {$studFile->research_type}.";
        }

        if ($studFile->keywords->isNotEmpty()) {
            $parts[] = 'Keywords: '.$studFile->keywords->pluck('display_name')->implode(', ').'.';
        }

        if ($studFile->sdgs->isNotEmpty()) {
            $parts[] = 'SDGs: '.$studFile->sdgs->pluck('label')->implode('; ').'.';
        }

        $abstract = $studFile->desc ?: $studFile->extracted_abstract;
        $parts[] = 'Abstract: '.($abstract ?: 'Not available.');

        return trim(implode(' ', $parts));
    }

    /**
     * smalot/pdfparser reads directly from a local filesystem path — it
     * has no concept of a remote disk. For the 'local' disk this is
     * already a real path; for anything else (e.g. S3/Supabase Storage)
     * the object is downloaded to a temp file first and parsed from
     * there. Caller is responsible for deleting the returned temp file
     * (the second tuple element, or null when none was created).
     *
     * @return array{0: string, 1: ?string}
     */
    private function resolveLocalPath(string $disk, string $path): array
    {
        if ($disk === 'local') {
            return [Storage::disk($disk)->path($path), null];
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'research_pdf_');
        file_put_contents($tempFile, Storage::disk($disk)->get($path));

        return [$tempFile, $tempFile];
    }

    /**
     * Resolves an embedding vector for every chunk, reusing one already
     * stored under the same content_hash — by any document, not just this
     * one — instead of re-embedding it. Chunking is deterministic, so an
     * unchanged section of a re-indexed document, or two submissions with
     * overlapping/duplicate content, land on the exact same hash and never
     * need a second OpenAI call.
     *
     * @param  array<int, array{chunk_index:int, section:?string, page_number:?int, content:string, token_count:int, content_hash:string}>  $chunks
     * @return array{rows: array<int, array<string, mixed>>, prompt_tokens: int, embedded_chunks: int}
     *         'rows' is each chunk plus an 'embedding_literal' key (a
     *         pgvector text literal, e.g. "[0.1,0.2,...]") ready for the
     *         ::vector cast. 'prompt_tokens' and 'embedded_chunks' cover
     *         only the chunks that actually went to the API — the ones
     *         served by content-hash reuse cost nothing and are excluded, so
     *         the caller's AiUsageLog row reports real spend.
     */
    private function resolveEmbeddings(array $chunks, EmbeddingService $embedder): array
    {
        $hashes = array_values(array_unique(array_column($chunks, 'content_hash')));

        // Reuse is only valid when both the embedding model AND the
        // configured embedding version match — content_hash alone can't
        // tell "this vector is current" apart from "this vector predates a
        // RESEARCH_EMBEDDING_VERSION bump" or a model swap, since unchanged
        // text hashes identically either way. Without this guard, bumping
        // the version to force a re-embed would silently keep reusing the
        // stale vectors it was meant to replace.
        $existing = DB::table('research_chunks')
            ->whereIn('content_hash', $hashes)
            ->where('embedding_model', $this->embeddingModel())
            ->where('embedding_version', $this->embeddingVersion())
            ->selectRaw('DISTINCT ON (content_hash) content_hash, embedding::text as embedding')
            ->orderBy('content_hash')
            ->get()
            ->keyBy('content_hash');

        $textByMissingHash = [];
        foreach ($chunks as $chunk) {
            $hash = $chunk['content_hash'];
            if (! $existing->has($hash) && ! isset($textByMissingHash[$hash])) {
                $textByMissingHash[$hash] = $chunk['content'];
            }
        }

        $newByHash = [];
        $promptTokens = 0;

        if (! empty($textByMissingHash)) {
            $missingHashes = array_keys($textByMissingHash);
            $result = $embedder->embedBatch(array_values($textByMissingHash));
            $vectors = $result['embeddings'];
            $promptTokens = $result['prompt_tokens'];

            if (count($vectors) !== count($missingHashes)) {
                throw new \RuntimeException('Embedding count did not match chunk count.');
            }

            $newByHash = array_combine($missingHashes, $vectors);
        }

        $rows = array_map(function (array $chunk) use ($existing, $newByHash) {
            $hash = $chunk['content_hash'];
            $chunk['embedding_literal'] = $existing->has($hash)
                ? $existing[$hash]->embedding
                : '['.implode(',', $newByHash[$hash]).']';

            return $chunk;
        }, $chunks);

        return [
            'rows' => $rows,
            'prompt_tokens' => $promptTokens,
            'embedded_chunks' => count($newByHash),
        ];
    }

    /**
     * Multi-row INSERTs (100 rows per statement) instead of one round-trip
     * per chunk — the database is a Supabase pooler in a different region,
     * so a document with hundreds of chunks previously meant hundreds of
     * sequential network round-trips just to write the index.
     *
     * @param  array<int, array<string, mixed>>  $rows  chunk data plus 'embedding_literal', from resolveEmbeddings()
     */
    private function insertChunksBatch(int $studFileId, array $rows): void
    {
        $now = now();
        $model = $this->embeddingModel();
        $version = $this->embeddingVersion();

        foreach (array_chunk($rows, 100) as $batch) {
            $placeholders = [];
            $bindings = [];

            foreach ($batch as $row) {
                $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?::vector, ?, ?)';
                array_push(
                    $bindings,
                    $studFileId,
                    $row['chunk_index'],
                    $row['section'],
                    $row['page_number'],
                    $row['content'],
                    $row['token_count'],
                    $row['content_hash'],
                    $model,
                    $version,
                    $row['embedding_literal'],
                    $now,
                    $now,
                );
            }

            DB::insert(
                'insert into research_chunks
                    (stud_file_id, chunk_index, section, page_number, content, token_count, content_hash, embedding_model, embedding_version, embedding, created_at, updated_at)
                 values '.implode(', ', $placeholders),
                $bindings
            );
        }
    }

    private function embeddingModel(): string
    {
        return (string) config('services.openai.embedding_model');
    }

    private function embeddingVersion(): int
    {
        return (int) config('research.embedding.version', 1);
    }
}
