<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoostTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $boostTypes = [
            [
                'name' => 'Highlight',
                'daily_rate' => 214.285,
                'display_order' => 1,
                'is_active' => true,
                'description' => 'Highlight your ad with a colored border to stand out',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Repeated',
                'daily_rate' => 500,
                'display_order' => 2,
                'is_active' => true,
                'description' => 'Repeat your ad multiple times for better visibility',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Top',
                'daily_rate' => 1071.428,
                'display_order' => 3,
                'is_active' => true,
                'description' => 'Keep your ad at the top of search results',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gallery',
                'daily_rate' => 1428.571,
                'display_order' => 4,
                'is_active' => true,
                'description' => 'Feature your ad in the premium gallery section',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('boost_types')->insert($boostTypes);
    }
}
