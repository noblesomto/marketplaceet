<?php

namespace Tests\Unit\Support;

use App\Support\PhoneSpecs;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PhoneSpecsTest extends TestCase
{
    /** Real seller text from Mobile Phones listings: title, description, [ram, storage]. */
    public static function listings(): array
    {
        return [
            'storage in title'         => ['iPhone 11 Pro Max 256gb', 'Factory unlock, battery health 94', [null, '256GB']],
            'spaced unit'              => ['Apple iPhone SE, 64 GB, Black - TOP condition', '', [null, '64GB']],
            'gig'                      => ['iPhone 12 prom max 256 gig blue good shape', '', [null, '256GB']],
            'snapped gig'              => ['Samsung S10 124gig 5G', 'Very clean', [null, '128GB']],
            'ram and rom glued'        => ['Tecno camon 18 p', 'Tecno camon 18 8GB RAM/128GB ROMWork perfectly good', ['8GB', '128GB']],
            'extended ram'             => ['ITEL P70', '<div>ITEL P70&nbsp;<br>4+4GB RAM&nbsp;<br>128GB ROM&nbsp;</div>', ['4GB', '128GB']],
            'ram label first'          => ['ZTE Blade A35', '<div>RAM 4 Gb + 8GB<br>STORAGE 64</div>', ['4GB', '64GB']],
            'ram glued to next word'   => ['Samsung galaxy S22 ultra', 'Samsung galaxy S22 ultra 5G 256GB storage 12GB ramblack color', ['12GB', '256GB']],
            'unitless rom'             => ['Clean used Google pixel 7 pro available 12gb ram 128rom', '', ['12GB', '128GB']],
            'gig ram and rom'          => ['Redmi 13C, 256GIG rom n 16GIG ram', '', ['16GB', '256GB']],
            'gram'                     => ['Google pixel 7 pro', '128gb 8gRAM 4g network', ['8GB', '128GB']],
            'slash pair'               => ['Samsung A71 For sale', 'Used As New, 6/128gb. Price N160, 000', ['6GB', '128GB']],
            'g pair'                   => ['Tecno LC8 64G', 'Tecno LC8 4G/64G Location Addis Ababa Bole', ['4GB', '64GB']],
            'storage then ram'         => ['Redmi A3 64gb', 'Redmi A3, 64gb/3gb ram, still in good condition', ['3GB', '64GB']],
            'two bare sizes'           => ['Xiaomi 13 12GB 512GB', '', ['12GB', '512GB']],
            'internal memory'          => ['Galaxy S21 Ultra 5G', 'Neatly used, 12gb ram, 128gb internal memory', ['12GB', '128GB']],
            'size glued to next word'  => ['Tecno spark 5 pro', 'TECNO SPARK 5 PRO 64GBNo cracks', [null, '64GB']],
            'title beats description'  => ['Uk Used Samsung S22 Ultra 128gb', 'All uk used 128gb 420k256gb 370k512gb 550k', [null, '128GB']],
            'colon labels'             => ['Samsung Galaxy A50', 'Selling Samsung Galaxy A50<br>RAM: 4gb<br>Storage: 128gb', ['4GB', '128GB']],
            'terabyte'                 => ['iPhone 15 Pro Max 1TB', '', [null, '1TB']],
            'pair before a full stop'  => ['Redmi K20 Pro', 'Redmi K20 PRO 8GB/126GB. Perfect working condition', ['8GB', '128GB']],
            'small bare size is ram'   => ['Samsung Galaxy A80 8gb 128gb', '', ['8GB', '128GB']],
            'glued to the word before' => ['Iphone X', 'Iphone X All perfect64gbGood Battery', [null, '64GB']],
            'glued sizes'              => ['Itel A70 for sale', 'Itel A70 with pouch, 8gb128gb, with 5000 mah', ['8GB', '128GB']],
            'glued after 5g'           => ['Brand new Samsung A26 5g', 'Brand new Samsung A26 5g6gb ram128gb storage', ['6GB', '128GB']],
            'first ram mention wins'   => ['Clean infinix hot 50 256gb', 'INFINIX HOT 50 PRO RAM 6GB expandable 16GB RAMCamara rear 50MP', ['6GB', '256GB']],
            'model number before size' => ['Iphone16', 'Iphone16/128gb', [null, '128GB']],
            'line breaks separate'     => ['Infinix hot 40 pro', 'Ram: 8gb can be extended to 12gb/16gb<br><br>Rom/internal memory: 256gb&nbsp;<br>', ['8GB', '256GB']],
            'nothing'                  => ['Iphone 13 Pro For Sale', 'Battery health is 80. Condition 9/10. Comes with case and box.', [null, null]],
            '5G is not a size'         => ['Samsung Galaxy A53 5G black', 'No signs of wear, 4G LTE, 5000mAh', [null, null]],
        ];
    }

    #[DataProvider('listings')]
    public function test_reads_specs_from_seller_text(string $title, string $description, array $expected): void
    {
        $specs = PhoneSpecs::parse($title, $description);

        $this->assertSame(
            array_combine(PhoneSpecs::FIELDS, $expected),
            array_merge(array_fill_keys(PhoneSpecs::FIELDS, null), $specs),
        );
    }

    public function test_unknown_ram_sizes_are_ignored(): void
    {
        $this->assertSame(['storage' => '256GB'], PhoneSpecs::parse('Redmi 14c 256GB', 'Redmi 14c for sale. 88gb RAM/256gb ROM'));
    }

    public function test_seller_choices_win_and_invalid_ones_fall_back_to_the_text(): void
    {
        $specs = PhoneSpecs::resolve(['ram' => '7GB', 'storage' => '512GB'], 'Infinix Hot 40', '8gb ram 256gb rom');

        $this->assertSame(['ram' => '8GB', 'storage' => '512GB'], $specs);
    }

    public function test_nothing_found_resolves_to_nulls(): void
    {
        $this->assertSame(['ram' => null, 'storage' => null], PhoneSpecs::resolve([], 'Clean iPhone for sale'));
    }
}
