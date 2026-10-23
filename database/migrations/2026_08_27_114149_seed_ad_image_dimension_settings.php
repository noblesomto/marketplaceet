<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'image_min_width'          => '800',
            'image_min_height'         => '600',
            'image_recommended_width'  => '1200',
            'image_recommended_height' => '900',
            'image_min_file_size_kb'   => '50',
            'image_max_file_size_mb'   => '20',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('ad_settings')->insertOrIgnore([
                'key'        => $key,
                'value'      => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('ad_settings')->whereIn('key', [
            'image_min_width',
            'image_min_height',
            'image_recommended_width',
            'image_recommended_height',
            'image_min_file_size_kb',
            'image_max_file_size_mb',
        ])->delete();
    }
};
