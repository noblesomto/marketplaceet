<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * This IS the Ethiopia site, so the sister-sites flag row should link to
 * Nigeria instead of to itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        $nigeria = [
            'country_name' => 'Nigeria',
            'flag'         => 'frontend/flags/nigeria.png',
            'url'          => 'https://www.marketplace.ng',
            'updated_at'   => now(),
        ];

        $ethiopia = DB::table('sister_sites')
            ->where('country_name', 'Ethiopia')
            ->orWhere('flag', 'frontend/flags/ethiopia.png')
            ->first();

        if (DB::table('sister_sites')->where('country_name', 'Nigeria')->exists()) {
            if ($ethiopia) {
                DB::table('sister_sites')->where('id', $ethiopia->id)->delete();
            }
        } elseif ($ethiopia) {
            DB::table('sister_sites')->where('id', $ethiopia->id)->update($nigeria);
        } else {
            DB::table('sister_sites')->insert($nigeria + ['display_order' => 1, 'is_active' => true, 'created_at' => now()]);
        }

        Cache::forget('sister_sites_active');
    }

    public function down(): void
    {
        DB::table('sister_sites')->where('country_name', 'Nigeria')->update([
            'country_name' => 'Ethiopia',
            'flag'         => 'frontend/flags/ethiopia.png',
            'url'          => 'https://marketplace.com.et',
            'updated_at'   => now(),
        ]);

        Cache::forget('sister_sites_active');
    }
};
