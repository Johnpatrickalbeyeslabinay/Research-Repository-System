<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One AI-chatbot conversation. stud_file_id set = a single-research
     * chat (research/show's "Ask about this research", retrieval pinned to
     * that document); null = repository-wide.
     *
     * active_context replaces an LLM-generated rolling summary — it's a
     * small, deterministically-updated JSON blob (topic, active filters,
     * the research ids last discussed) that costs zero extra API calls and
     * is what lets a follow-up like "which one used CNN?" resolve against
     * the right documents. See App\Services\Chat\ConversationContext and
     * App\Services\Retrieval\QueryPlanner's follow-up scope handling.
     */
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('stud_file_id')->nullable()->constrained('stud_file')->cascadeOnDelete();
            $table->string('title', 160)->nullable();
            $table->jsonb('active_context')->nullable();
            $table->unsignedSmallInteger('message_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
