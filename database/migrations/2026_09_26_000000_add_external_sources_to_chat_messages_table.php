<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Publications from an outside open-access index that an answer mentioned.
     *
     * A SEPARATE column from `citations`, not extra rows inside it, and that
     * separation is the point rather than a schema preference. `citations`
     * means "a study in this repository, which the asking user is allowed to
     * read, at a known page of a known section". An external work has none of
     * those properties: nobody here owns it, ResearchAccess has no opinion on
     * it, and only its title and abstract were ever fetched.
     *
     * Keeping them in one column would mean every consumer — the widget, the
     * history replay, any future export — has to remember to check a `source`
     * discriminator, and the first one that forgets presents an outside paper
     * as one of this repository's holdings. Two columns make that mistake
     * impossible to make by omission.
     *
     * Shape: [{n: "E1", source: "external", provider, title, authors[], year,
     * venue, doi, url}]. The n is E-prefixed so it can never collide with an
     * internal [1] — see App\Services\Chat\ExternalCitationBuilder.
     */
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->jsonb('external_sources')->nullable()->after('citations');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn('external_sources');
        });
    }
};
