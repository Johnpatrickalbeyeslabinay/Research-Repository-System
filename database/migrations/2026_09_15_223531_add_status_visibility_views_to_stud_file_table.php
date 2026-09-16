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
            if (! Schema::hasColumn('stud_file', 'status')) {
                $table->string('status')->default('pending')->after('auto_tag');
            }

            if (! Schema::hasColumn('stud_file', 'visibility')) {
                $table->string('visibility')->default('private')->after('status');
            }

            if (! Schema::hasColumn('stud_file', 'views')) {
                $table->integer('views')->default(0)->after('downloads');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stud_file', function (Blueprint $table) {
            foreach (['views', 'visibility', 'status'] as $column) {
                if (Schema::hasColumn('stud_file', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
