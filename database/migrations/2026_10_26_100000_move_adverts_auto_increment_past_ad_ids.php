<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * New adverts take ad_id = id, but older adverts have random 5-digit ad_ids
 * (10000–99999) ahead of the current ids, so a new id could already be
 * someone's ad_id and posting failed on adverts_ad_id_unique. Start new ids
 * after the highest ad_id so they never meet.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $next = (int) DB::table('adverts')
            ->selectRaw('GREATEST(COALESCE(MAX(id), 0), COALESCE(MAX(CAST(ad_id AS UNSIGNED)), 0)) + 1 AS next')
            ->value('next');

        DB::statement('ALTER TABLE adverts AUTO_INCREMENT = ' . max($next, 1));
    }

    public function down(): void
    {
        // AUTO_INCREMENT can't move back below existing rows; nothing to undo.
    }
};
