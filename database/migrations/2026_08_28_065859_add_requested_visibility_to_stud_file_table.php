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
        Schema::table('stud_file', function (Blueprint $table) {
            $table->string('requested_visibility')
                ->default('private')
                ->after('visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stud_file', function (Blueprint $table) {
            $table->dropColumn('requested_visibility');
        });
    }
};
