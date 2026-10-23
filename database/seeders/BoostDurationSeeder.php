<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoostDurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $durations = [
            [
                'days' => 7,
                'discount_percentage' => 0,
                'label' => '7 Days',
                'display_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'days' => 14,
                'discount_percentage' => 3,
                'label' => '14 Days (3% off)',
                'display_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'days' => 30,
                'discount_percentage' => 5,
                'label' => '30 Days (5% off)',
                'display_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'days' => 90,
                'discount_percentage' => 7,
                'label' => '90 Days (7% off)',
                'display_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'days' => 120,
                'discount_percentage' => 10,
                'label' => '120 Days (10% off)',
                'display_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('boost_durations')->insert($durations);
    }
}
