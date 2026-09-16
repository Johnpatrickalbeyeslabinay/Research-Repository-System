<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a researcher be recorded even when they have no user account —
 * previously stud_file_author.user_id was a hard, non-nullable FK to
 * users, so a free-typed name could never be stored (see the "Submit
 * New Research" redesign: researchers are now added by name, not picked
 * from an auto-populated list).
 *
 * No doctrine/dbal in this project (composer.json), so column-type/
 * nullability changes on Postgres go through raw SQL rather than
 * Blueprint::change() — same approach as
 * 2026_09_15_230000_enable_pgvector_extension.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop the FK before altering nullability — Postgres allows
        // ALTER COLUMN ... DROP NOT NULL directly, but doing it via
        // Schema::table lets us also drop the now-meaningless unique
        // pair constraint in the same migration.
        Schema::table('stud_file_author', function (Blueprint $table) {
            $table->dropUnique(['stud_file_id', 'user_id']);
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE stud_file_author ALTER COLUMN user_id DROP NOT NULL');

        Schema::table('stud_file_author', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->string('author_name')->nullable()->after('user_id');
            $table->unsignedSmallInteger('position')->default(0)->after('role_label');
        });

        // Backfill existing rows (all currently linked to a real user)
        // so author_name is never null for data written before this
        // migration.
        DB::statement(
            'UPDATE stud_file_author sa SET author_name = u.name FROM users u WHERE u.id = sa.user_id AND sa.author_name IS NULL'
        );
    }

    public function down(): void
    {
        Schema::table('stud_file_author', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'position']);
            $table->dropForeign(['user_id']);
        });

        DB::statement('DELETE FROM stud_file_author WHERE user_id IS NULL');
        DB::statement('ALTER TABLE stud_file_author ALTER COLUMN user_id SET NOT NULL');

        Schema::table('stud_file_author', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['stud_file_id', 'user_id']);
        });
    }
};
