<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The 17 UN Sustainable Development Goals, keyed by their official number.
 * Nothing else populates this table (see the migration at
 * 2026_09_09_095915_create_sdgs_table.php) — without it, the SDG picker on
 * the submission form has nothing to render and the AI's SDG suggestions
 * have no row to resolve against.
 *
 * updateOrInsert keyed on 'number' rather than a blind insert so re-running
 * this seeder (e.g. after a fresh migrate) never duplicates rows.
 */
class SdgSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        for ($number = 1; $number <= 17; $number++) {
            if (DB::table('sdgs')->where('number', $number)->exists()) {
                continue;
            }

            DB::table('sdgs')->insert([
                'number' => $number,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
