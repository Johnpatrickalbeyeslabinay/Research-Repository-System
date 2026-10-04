<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stud_file', function (Blueprint $table) {
            $table->unsignedTinyInteger('resubmit_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('stud_file', function (Blueprint $table) {
            $table->dropColumn('resubmit_count');
        });
    }
};
