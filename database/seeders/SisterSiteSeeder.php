<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SisterSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sisterSites = [
            [
                'country_name' => 'Ethiopia',
                'flag' => 'frontend/flags/ethiopia.png',
                'url' => 'https://marketplace.com.et',
                'display_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_name' => 'Ghana',
                'flag' => 'frontend/flags/ghana.png',
                'url' => 'https://marketplace.com.gh',
                'display_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_name' => 'Uganda',
                'flag' => 'frontend/flags/uganda.png',
                'url' => 'https://marketplace.ug',
                'display_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_name' => 'Kenya',
                'flag' => 'frontend/flags/kenya.png',
                'url' => 'https://marketplaceke.co.ke',
                'display_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_name' => 'Canada',
                'flag' => 'frontend/flags/canada.png',
                'url' => 'https://marketplaceca.ca',
                'display_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_name' => 'UAE',
                'flag' => 'frontend/flags/united_arab_emirates.png',
                'url' => 'https://marketplaceuae.com',
                'display_order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('sister_sites')->insert($sisterSites);
    }
}
