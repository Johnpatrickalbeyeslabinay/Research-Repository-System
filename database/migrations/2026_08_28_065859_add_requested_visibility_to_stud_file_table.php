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
        if (! Schema::hasColumn('stud_file', 'requested_visibility')) {
            Schema::table('stud_file', function (Blueprint $table) {
                $table->string('requested_visibility')
                    ->default('private')
                    ->after('visibility');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('stud_file', 'requested_visibility')) {
            Schema::table('stud_file', function (Blueprint $table) {
                $table->dropColumn('requested_visibility');
            });
        }
    }
};
