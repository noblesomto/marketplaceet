<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        // Ethiopia's 12 regional states + 2 chartered cities (2023 administrative structure).
        // `station_id` is a legacy column from the old Nigeria/Agility courier integration
        // (now disabled) kept only to satisfy the existing NOT NULL schema; it is unused.
        $states = [
            ['id' =>  1, 'name' => 'Addis Ababa',                        'station_id' =>  1, 'slug' => 'addis-ababa'],
            ['id' =>  2, 'name' => 'Afar',                               'station_id' =>  2, 'slug' => 'afar'],
            ['id' =>  3, 'name' => 'Amhara',                             'station_id' =>  3, 'slug' => 'amhara'],
            ['id' =>  4, 'name' => 'Benishangul-Gumuz',                  'station_id' =>  4, 'slug' => 'benishangul-gumuz'],
            ['id' =>  5, 'name' => 'Central Ethiopia',                   'station_id' =>  5, 'slug' => 'central-ethiopia'],
            ['id' =>  6, 'name' => 'Dire Dawa',                          'station_id' =>  6, 'slug' => 'dire-dawa'],
            ['id' =>  7, 'name' => 'Gambela',                            'station_id' =>  7, 'slug' => 'gambela'],
            ['id' =>  8, 'name' => 'Harari',                             'station_id' =>  8, 'slug' => 'harari'],
            ['id' =>  9, 'name' => 'Oromia',                             'station_id' =>  9, 'slug' => 'oromia'],
            ['id' => 10, 'name' => 'Sidama',                             'station_id' => 10, 'slug' => 'sidama'],
            ['id' => 11, 'name' => 'Somali',                             'station_id' => 11, 'slug' => 'somali'],
            ['id' => 12, 'name' => 'South Ethiopia',                     'station_id' => 12, 'slug' => 'south-ethiopia'],
            ['id' => 13, 'name' => "South West Ethiopia Peoples'",       'station_id' => 13, 'slug' => 'south-west-ethiopia-peoples'],
            ['id' => 14, 'name' => 'Tigray',                             'station_id' => 14, 'slug' => 'tigray'],
        ];

        $now = now();
        $rows = array_map(fn($s) => array_merge($s, [
            'created_at' => $now,
            'updated_at' => $now,
        ]), $states);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('states')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('states')->insert($rows);
    }
}
