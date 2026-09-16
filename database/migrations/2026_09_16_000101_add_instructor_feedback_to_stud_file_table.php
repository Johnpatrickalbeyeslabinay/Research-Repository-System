<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stud_file', function (Blueprint $table) {
            $table->text('instructor_feedback')->nullable()->after('search_index_error');
        });
    }

    public function down(): void
    {
        Schema::table('stud_file', function (Blueprint $table) {
            $table->dropColumn('instructor_feedback');
        });
    }
};
