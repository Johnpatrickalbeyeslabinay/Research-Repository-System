<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supports App\Jobs\ProcessResearchDocument's embedding-reuse lookup
     * (WHERE content_hash IN (...)), which otherwise scans the whole table
     * on every document indexed. Chunk content is byte-for-byte identical
     * only for unchanged sections of a re-indexed document, or overlapping
     * content across documents — but the point of the lookup is to make
     * that check cheap, which it can't be without this index.
     */
    public function up(): void
    {
        Schema::table('research_chunks', function (Blueprint $table) {
            $table->index('content_hash');
        });
    }

    public function down(): void
    {
        Schema::table('research_chunks', function (Blueprint $table) {
            $table->dropIndex(['content_hash']);
        });
    }
};
