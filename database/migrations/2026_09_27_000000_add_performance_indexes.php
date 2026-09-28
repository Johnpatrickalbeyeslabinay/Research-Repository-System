<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Postgres does not index foreign keys on its own, so every dashboard
     * filter below — a student's own submissions, an adviser's queue, the
     * admin's status tabs — was a full scan of stud_file on each page load.
     * Composite (fk, status) because every one of those queries filters on
     * both; the leading column still serves plain fk lookups.
     */
    public function up(): void
    {
        Schema::table('stud_file', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
            $table->index(['adviser_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        // The (stud_file_id, …) side of each pivot is already covered by
        // its unique/primary key; the reverse direction (SDG filter on the
        // repository listing, a co-author's access check) was not.
        Schema::table('stud_file_sdg', function (Blueprint $table) {
            $table->index('sdg_id');
        });

        Schema::table('stud_file_author', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('stud_file', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['adviser_id', 'status']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('stud_file_sdg', function (Blueprint $table) {
            $table->dropIndex(['sdg_id']);
        });

        Schema::table('stud_file_author', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
