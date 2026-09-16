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
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // lowercase, normalized
            $table->string('display_name');
            $table->timestamps();
        });

        Schema::create('stud_file_keyword', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stud_file_id')
                ->constrained('stud_file')
                ->onDelete('cascade');

            $table->foreignId('keyword_id')
                ->constrained('keywords')
                ->onDelete('cascade');

            $table->string('source')->default('manual'); // extracted|ai_suggested|manual

            $table->unique(['stud_file_id', 'keyword_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stud_file_keyword');
        Schema::dropIfExists('keywords');
    }
};
