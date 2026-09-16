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
        Schema::create('stud_file_author', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stud_file_id')
                ->constrained('stud_file')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('role_label')->nullable(); // e.g. "Co-Researcher"

            $table->timestamps();

            $table->unique(['stud_file_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stud_file_author');
    }
};
