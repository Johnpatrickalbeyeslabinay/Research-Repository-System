<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('research_chunks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stud_file_id')
                ->constrained('stud_file')
                ->onDelete('cascade');

            $table->unsignedInteger('chunk_index');
            $table->string('section')->nullable();
            $table->unsignedInteger('page_number')->nullable();
            $table->text('content');
            $table->unsignedInteger('token_count')->nullable();
            $table->string('content_hash', 64)->nullable();

            $table->timestamps();
        });

        // pgvector has no native Laravel Schema-builder column type, so the
        // embedding column and its similarity index are added via raw SQL.
        // text-embedding-3-small produces 1536-dimensional vectors.
        //
        // Guarded to Postgres only — a non-Postgres driver (e.g. a stray
        // sqlite test run) still gets the base table above, just without
        // vector search; this app only ever runs against Postgres in
        // practice. Belt-and-braces, not a substitute for pointing tests
        // at a real Postgres+pgvector database.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE research_chunks ADD COLUMN embedding vector(1536)');

            DB::statement(
                'CREATE INDEX research_chunks_embedding_hnsw_idx ON research_chunks '.
                'USING hnsw (embedding vector_cosine_ops)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_chunks');
    }
};
