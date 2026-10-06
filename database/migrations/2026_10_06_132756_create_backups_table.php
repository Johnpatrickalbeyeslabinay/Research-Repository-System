<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->enum('type', ['manual', 'automatic']);

            $table->string('file_name');
            $table->string('file_path');

            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedBigInteger('records_count')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
