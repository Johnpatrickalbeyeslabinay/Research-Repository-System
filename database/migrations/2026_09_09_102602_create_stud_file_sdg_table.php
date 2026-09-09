<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stud_file_sdg', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stud_file_id')
                ->constrained('stud_file')
                ->onDelete('cascade');

            $table->foreignId('sdg_id')
                ->constrained('sdgs')
                ->onDelete('cascade');

            $table->unique(['stud_file_id', 'sdg_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stud_file_sdg');
    }
};
