<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * OpenAI reports, on every chat completion, how many of the prompt
     * tokens it served from its own prefix cache
     * (usage.prompt_tokens_details.cached_tokens). Those bill at a
     * discounted rate, so without this column every cost figure derived
     * from ai_usage_logs overstates what was actually spent.
     *
     * Deliberately NOT a cost column. Rates live in config/ai_pricing.php
     * and App\Support\AiCost computes money at report time, so a price
     * change re-prices history instead of leaving stale numbers in rows
     * nobody can tell are stale.
     *
     * Nullable, because it is genuinely unknown rather than zero for the
     * operations that do not report it: embeddings, and any row written
     * before this column existed. A default of 0 would have quietly
     * asserted "no tokens were cached" about turns nobody measured.
     */
    public function up(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->unsignedInteger('cached_prompt_tokens')->nullable()->after('prompt_tokens');
        });
    }

    public function down(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->dropColumn('cached_prompt_tokens');
        });
    }
};
