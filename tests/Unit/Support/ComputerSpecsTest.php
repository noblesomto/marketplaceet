<?php

namespace Tests\Unit\Support;

use App\Support\ComputerSpecs;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ComputerSpecsTest extends TestCase
{
    /** Real seller text from Laptops & Computers listings. */
    public static function listings(): array
    {
        return [
            'labelled'               => ['Intel Core i5 processor • 8 GB RAM • 256 GB SSD', ['Intel Core i5', '8GB', '256GB', 'SSD']],
            'glued together'         => ['UK used hp elitebook 820 G3 12inches Corei58GB RAM256GB SSD', ['Intel Core i5', '8GB', '256GB', 'SSD']],
            'slash pair'             => ['DELL XPS 9570 / Core i7 / 16/512 / 8th generation', ['Intel Core i7', '16GB', '512GB', null]],
            'bare sizes'             => ['HP Elitebook 840 G4 Core i5 7th Gen. 256GB 8GB Touchscreen', ['Intel Core i5', '8GB', '256GB', null]],
            'model number processor' => ['HP ProBook 430 G7 i5-10210u 8GB 256GB SSD', ['Intel Core i5', '8GB', '256GB', 'SSD']],
            'ryzen'                  => ['AMD Ryzen 7 5700U / 16 GB DDR4 RAM / 512 GB SSD', ['AMD Ryzen 7', '16GB', '512GB', 'SSD']],
            'apple silicon'          => ['MACBOOK AIR M1 (2020) 16GB / 512GB Chip Apple M1 RAM 16GB Unified Memory Storage 512GB SSD', ['Apple M1', '16GB', '512GB', 'SSD']],
            'hdd and gig'            => ['Complete HP corei5 desktop computer 4gb Ram, 500gb Hard disk', ['Intel Core i5', '4GB', '500GB', 'HDD']],
            'snapped size'           => ['Lenovo ThinkPad 16GB RAM, 238GB SSD', [null, '16GB', '256GB', 'SSD']],
            'terabyte'               => ['Dell Alienware x15 R1Core i7 11th gen32gb Ram1tb ssd', ['Intel Core i7', '32GB', '1TB', 'SSD']],
            'both drives'            => ['Core i3 7th gen. 8gb ram, 128gb ssd and 500gb hdd', ['Intel Core i3', '8GB', '128GB', 'SSD + HDD']],
            'emmc chromebook'        => ['DELL 3100 CHROMEBOOK LAPTOPS 4GB RAM 16GB eMMC STORAGE', [null, '4GB', '16GB', 'eMMC']],
            'celeron'                => ['HP 250 G7 Intel Celeron N4000 processor, 8GB DDR4 RAM, 256GB SSD', ['Intel Celeron', '8GB', '256GB', 'SSD']],
            'key value'              => ['Processor 12th Gen Intel (R) Core (TM) i7 1270P RAM 16 GBStorage 512 GB Graphics Card 128 MB', ['Intel Core i7', '16GB', '512GB', null]],
            'nothing'                => ['Neatly used dell laptop for sale', [null, null, null, null]],
        ];
    }

    #[DataProvider('listings')]
    public function test_reads_specs_from_seller_text(string $text, array $expected): void
    {
        $specs = ComputerSpecs::parse($text);

        $this->assertSame(
            array_combine(ComputerSpecs::FIELDS, $expected),
            array_merge(array_fill_keys(ComputerSpecs::FIELDS, null), $specs),
        );
    }

    public function test_graphics_memory_is_not_ram(): void
    {
        $this->assertSame('16GB', ComputerSpecs::parse('Core i7 GTX 1050 4gb dedicated graphics 16gb ram 512 ssd')['ram']);
        $this->assertSame('8GB', ComputerSpecs::parse('Ryzen 52gb Radeon Graphics card8gb Ram 256gb ssd')['ram']);
        $this->assertSame('AMD Ryzen 5', ComputerSpecs::parse('Ryzen 52gb Radeon Graphics card8gb Ram 256gb ssd')['processor']);
    }

    public function test_years_and_screens_are_not_storage(): void
    {
        $this->assertArrayNotHasKey('storage', ComputerSpecs::parse('MacBook Pro from 2015, 13.3-inch Retina display'));
        $this->assertArrayNotHasKey('storage', ComputerSpecs::parse('8GB Ram ROM SSD 1080 Screen display'));
    }

    public function test_seller_choices_win_and_invalid_ones_fall_back_to_the_text(): void
    {
        $specs = ComputerSpecs::resolve(
            ['processor' => 'Intel Core i7', 'ram' => '7GB', 'storage' => ''],
            'Core i5 8GB RAM 256GB SSD',
        );

        $this->assertSame(['processor' => 'Intel Core i7', 'ram' => '8GB', 'storage' => '256GB', 'storage_type' => 'SSD'], $specs);
    }
}
