<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marks a message as "keep it in the transcript, keep it out of the
     * prompt".
     *
     * The zero-token paths (greeting, acknowledgement, platform/no-evidence
     * canned replies) persist an assistant message like any other turn, and
     * App\Services\Chat\PromptBuilder::trimmedHistory() then re-sent them to
     * the model on every later turn. Two greetings early in a conversation
     * burned 4 of the 8 available history slots on content carrying no
     * information at all.
     *
     * The no-evidence reply is worse than merely wasteful: leaving
     * "I couldn't find enough information in the research repository..."
     * sitting in the history window actively teaches the model to decline,
     * so one failed lookup makes the next few answers more likely to give up
     * too.
     *
     * A real column rather than a predicate on the existing retrieval_meta
     * jsonb, for two reasons: jsonb containment behaves differently on
     * sqlite (which is what phpunit.xml points the tests at) than on
     * Postgres, and the existing (conversation_id, id) index cannot serve a
     * jsonb filter — so the history query would stop using an index on the
     * one query every single chat turn runs.
     *
     * IMPORTANT: this is prompt-only. ChatController::show() and the chat
     * widget's history must keep returning these rows — the user said "hi"
     * and saw a reply, and their transcript has to reflect that.
     */
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('excluded_from_history')->default(false)->after('role');

            // Covers trimmedHistory()'s WHERE conversation_id = ?
            // AND excluded_from_history = false ORDER BY id DESC.
            $table->index(
                ['conversation_id', 'excluded_from_history', 'id'],
                'chat_messages_history_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('chat_messages_history_idx');
            $table->dropColumn('excluded_from_history');
        });
    }
};
