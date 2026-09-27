<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_feedback', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stud_file_id')
                ->constrained('stud_file')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('feedback');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_feedback');
    }
};
