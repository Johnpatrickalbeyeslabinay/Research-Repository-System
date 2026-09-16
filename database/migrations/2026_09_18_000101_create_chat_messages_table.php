<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One turn (role='user' or 'assistant') in a chat_conversations thread.
     *
     * citations is jsonb rather than a separate table — it's always read
     * together with the message that produced it and never queried
     * independently, so a join would add cost for no benefit:
     * [{n, stud_file_id, title, authors:[], year, section, page_number, chunk_id, url}]
     *
     * retrieval_meta records what retrieval actually did for this turn —
     * {chunks_considered, chunks_used, documents_used, filters,
     * no_evidence, relaxed_filters} — for debugging and for
     * App\Console\Commands\EvaluateChatbotCommand, without having to log
     * question/answer text anywhere sensitive (see ai_usage_logs, which
     * deliberately never stores either).
     */
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->string('role', 16);
            $table->text('content');
            $table->jsonb('citations')->nullable();
            $table->jsonb('retrieval_meta')->nullable();
            $table->string('model', 64)->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
