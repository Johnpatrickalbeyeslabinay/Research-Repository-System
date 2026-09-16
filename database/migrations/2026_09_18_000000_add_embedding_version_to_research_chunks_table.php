<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets App\Jobs\ProcessResearchDocument tell "this vector is still
     * current for the configured model/chunking strategy" apart from "this
     * vector predates a change and needs re-embedding" — content_hash alone
     * can't make that distinction, since the same text hashes the same
     * regardless of which model or chunking version produced the chunk
     * sitting next to it. php artisan research:index-backfill uses this to
     * find every document that needs re-embedding after
     * RESEARCH_EMBEDDING_VERSION or OPENAI_EMBEDDING_MODEL changes.
     */
    public function up(): void
    {
        Schema::table('research_chunks', function (Blueprint $table) {
            $table->string('embedding_model', 64)->nullable()->after('content_hash');
            $table->unsignedSmallInteger('embedding_version')->default(1)->after('embedding_model');

            // Single-research chat scope (VectorSearchService: WHERE
            // stud_file_id = ? ORDER BY chunk_index) and the ordered
            // display of a document's own chunks.
            $table->index(['stud_file_id', 'chunk_index']);

            // Backs the backfill command's "which documents are stale"
            // lookup — WHERE embedding_version <> ? OR embedding_model <> ?.
            $table->index(['embedding_version', 'embedding_model'], 'research_chunks_embedding_ver_idx');
        });
    }

    public function down(): void
    {
        Schema::table('research_chunks', function (Blueprint $table) {
            $table->dropIndex('research_chunks_embedding_ver_idx');
            $table->dropIndex(['stud_file_id', 'chunk_index']);
            $table->dropColumn(['embedding_model', 'embedding_version']);
        });
    }
};
