<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stud_file', function (Blueprint $table) {
            // Research Information
            $table->string('research_type')->nullable()->after('title');
            $table->unsignedSmallInteger('publication_year')->nullable()->after('research_type');
            $table->foreignId('adviser_id')->nullable()->after('adviser')
                ->constrained('users')->nullOnDelete();

            // PDF / extraction
            $table->string('pdf_hash', 64)->nullable()->after('file_path')->index();
            $table->unsignedSmallInteger('pdf_page_count')->nullable()->after('pdf_hash');
            $table->text('extracted_abstract')->nullable()->after('desc');
            $table->string('abstract_source')->default('manual')->after('extracted_abstract');
            $table->jsonb('extracted_sections')->nullable()->after('abstract_source');

            // Metadata AI
            $table->string('ai_analysis_status')->default('not_started')->after('extracted_sections');
            $table->string('ai_analysis_fingerprint')->nullable()->after('ai_analysis_status');
            $table->jsonb('ai_suggested_keywords')->nullable()->after('ai_analysis_fingerprint');
            $table->jsonb('ai_suggested_sdgs')->nullable()->after('ai_suggested_keywords');
            $table->string('ai_model_used')->nullable()->after('ai_suggested_sdgs');
            $table->timestamp('ai_analyzed_at')->nullable()->after('ai_model_used');

            // Document indexing (RAG pipeline)
            $table->string('search_index_status')->default('not_started')->after('ai_analyzed_at');
            $table->timestamp('search_indexed_at')->nullable()->after('search_index_status');
            $table->text('search_index_error')->nullable()->after('search_indexed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stud_file', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adviser_id');

            $table->dropColumn([
                'research_type',
                'publication_year',
                'pdf_hash',
                'pdf_page_count',
                'extracted_abstract',
                'abstract_source',
                'extracted_sections',
                'ai_analysis_status',
                'ai_analysis_fingerprint',
                'ai_suggested_keywords',
                'ai_suggested_sdgs',
                'ai_model_used',
                'ai_analyzed_at',
                'search_index_status',
                'search_indexed_at',
                'search_index_error',
            ]);
        });
    }
};
