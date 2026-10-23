<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LgaSeeder extends Seeder
{
    public function run(): void
    {
        // Zones / sub-cities / special woredas per region (state_id matches StateSeeder, 1-14).
        // The `lgas` table name/class is kept as the underlying "zone" table to avoid
        // renaming it across the codebase (Nigeria's LGA level is Ethiopia's zone level).
        $data = [
            1  => ['Addis Ketema','Akaky Kaliti','Arada','Bole','Gullele','Kirkos','Kolfe Keranio','Lideta','Nifas Silk-Lafto','Yeka','Lemi Kura'],
            2  => ['Zone 1 (Awsi Rasu)','Zone 2 (Kilbet Rasu)','Zone 3 (Gabi Rasu)','Zone 4 (Fantana Rasu)','Zone 5 (Hari Rasu)'],
            3  => ['Awi','Bahir Dar Special Zone','East Gojjam','North Gondar','North Shewa','North Wollo','Oromia Zone','South Gondar','South Wollo','Wag Hemra','West Gojjam'],
            4  => ['Asosa','Kamashi','Mao-Komo Special Woreda','Metekel','Pawe Special Woreda'],
            5  => ['Gurage','Halaba','Hadiya','Kembata Tembaro',"Silt'e",'Yem'],
            6  => ['Dire Dawa City','Dire Dawa Rural'],
            7  => ['Agnuak','Nuer','Majang','Mezhenger','Etang Special Woreda'],
            8  => ['Harari'],
            9  => ['Arsi','Bale','Borena','Buno Bedele','East Hararghe','East Shewa','East Wellega','Guji','Horo Guduru Wellega','Ilubabor','Jimma','Kelem Wellega','North Shewa','Southwest Shewa','West Arsi','West Guji','West Hararghe','West Shewa','West Wellega','Finfinne Special Zone'],
            10 => ['Hawassa City','Aleta Wondo','Boricha','Dale','Wondo Genet','Bensa','Malga','Shebedino','Hula','Dara'],
            11 => ['Afder','Daawa','Doolo','Erer','Fafan','Jarar','Korahe','Liben','Nogob','Shabelle','Siti'],
            12 => ['Gedeo','Gofa','Konso','Burji','South Omo','Wolaita','Amaro','Basketo','Derashe','Ale'],
            13 => ['Bench Sheko','Dawro','Kaffa','Sheka','West Omo','Konta Special Woreda'],
            14 => ['Central Tigray','Eastern Tigray','Mekelle','North Western Tigray','Southern Tigray','Southeastern Tigray','Western Tigray'],
        ];

        DB::table('lgas')->truncate();

        $now = now();
        $rows = [];
        foreach ($data as $stateId => $zones) {
            foreach ($zones as $name) {
                $rows[] = [
                    'state_id'   => $stateId,
                    'name'       => $name,
                    'slug'       => Str::slug($name),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('lgas')->insert($chunk);
        }

        $this->command->info('Seeded ' . count($rows) . ' zones across 14 regions.');
    }
}
