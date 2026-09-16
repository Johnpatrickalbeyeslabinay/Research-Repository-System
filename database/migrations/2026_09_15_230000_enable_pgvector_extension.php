<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * pgvector-only DDL — guarded so a non-Postgres driver (e.g. a stray
     * sqlite test run) degrades to "no vector search" instead of a hard
     * failure. This app only ever runs against Postgres in practice
     * (DB_CONNECTION=pgsql everywhere); the guard is a safety net, not a
     * substitute for pointing tests at a real Postgres+pgvector database.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP EXTENSION IF EXISTS vector');
    }
};
