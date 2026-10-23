<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Reassign new unique ad_ids to all duplicate rows (keep the earliest by id)
        $duplicates = DB::select("
            SELECT id, ad_id
            FROM adverts
            WHERE ad_id IN (
                SELECT ad_id FROM adverts GROUP BY ad_id HAVING COUNT(*) > 1
            )
            ORDER BY ad_id, id
        ");

        $seen = [];
        $usedIds = DB::table('adverts')->pluck('ad_id')->flip()->all();

        foreach ($duplicates as $row) {
            if (!isset($seen[$row->ad_id])) {
                // First occurrence — keep it
                $seen[$row->ad_id] = true;
                continue;
            }

            // Duplicate — assign a fresh unique ad_id
            do {
                $newId = rand(10000, 99999);
            } while (isset($usedIds[$newId]));

            $usedIds[$newId] = true;

            DB::table('adverts')->where('id', $row->id)->update(['ad_id' => (string) $newId]);
        }

        // Step 2: Add unique index
        Schema::table('adverts', function (Blueprint $table) {
            $table->unique('ad_id');
        });
    }

    public function down(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            $table->dropUnique(['ad_id']);
        });
    }
};
