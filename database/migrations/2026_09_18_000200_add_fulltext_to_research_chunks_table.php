<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The lexical arm of hybrid search (App\Services\Retrieval\LexicalSearchService).
     * Vector search alone handles topical questions well but exact
     * identifiers poorly — a study's own acronym, an author's exact
     * spelling, a title fragment. content_tsv is a generated column (kept
     * in sync automatically, never written to directly) so it can't drift
     * from `content`.
     *
     * Guarded to Postgres only — same convention as
     * 2026_09_15_230000_enable_pgvector_extension.php and
     * 2026_09_15_230004_create_research_chunks_table.php. This app only
     * ever runs against Postgres in practice.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(
            "ALTER TABLE research_chunks ADD COLUMN content_tsv tsvector ".
            "GENERATED ALWAYS AS (to_tsvector('english', content)) STORED"
        );

        DB::statement('CREATE INDEX research_chunks_content_tsv_idx ON research_chunks USING GIN (content_tsv)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS research_chunks_content_tsv_idx');
        DB::statement('ALTER TABLE research_chunks DROP COLUMN IF EXISTS content_tsv');
    }
};
