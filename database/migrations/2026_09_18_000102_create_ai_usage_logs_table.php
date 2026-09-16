<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-call AI usage/cost telemetry — every OpenAI call this app makes
     * (chat answers, query/document embeddings, metadata suggestions)
     * writes one row here. Deliberately never stores question/answer text
     * or document content: that already lives under the user's own
     * chat_messages row (or isn't persisted at all for embeddings), and
     * this table exists purely for cost/latency/failure visibility, not as
     * a second copy of conversation content.
     */
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->uuid('request_id');
            // 'query_embedding' | 'chat_answer' | 'doc_embedding' | 'metadata'
            $table->string('operation', 32);
            $table->string('model', 64);
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->unsignedSmallInteger('chunks_retrieved')->nullable();
            $table->boolean('success')->default(true);
            $table->string('error_code', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
