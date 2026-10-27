<?php

namespace Tests\Unit\Support;

use App\Support\DeviceModelMatcher;
use Tests\TestCase;

class DeviceModelMatcherTest extends TestCase
{
    private const IPHONES = [
        'iPhone 6', 'iPhone 6 Plus', 'iPhone 7', 'iPhone 7 Plus', 'iPhone X', 'iPhone XR',
        'iPhone XS', 'iPhone XS Max', 'iPhone SE', 'iPhone 11', 'iPhone 11 Pro', 'iPhone 11 Pro Max',
        'iPhone 12', 'iPhone 12 mini', 'iPhone 12 Pro', 'iPhone 12 Pro Max', 'iPhone 13 Pro Max',
        'iPhone 16e', 'iPhone 17 Pro Max',
    ];

    private const GALAXIES = [
        'Galaxy S8', 'Galaxy S8 Plus', 'Galaxy S10e', 'Galaxy S10', 'Galaxy S10 Plus', 'Galaxy S20', 'Galaxy S20 FE',
        'Galaxy S21', 'Galaxy S21 Ultra', 'Galaxy Note 10', 'Galaxy Note 10 Plus', 'Galaxy Note 10 Lite',
        'Galaxy A05', 'Galaxy A05s', 'Galaxy A06', 'Galaxy A07', 'Galaxy A14', 'Galaxy A16', 'Galaxy A25',
        'Galaxy Z Fold 4', 'Galaxy Z Flip 4', 'Galaxy S7 Edge',
    ];

    private function matcher(array $names = self::IPHONES, ?string $prefix = 'iPhone', array $brandWords = ['apple'], array $aliases = []): DeviceModelMatcher
    {
        $models = collect($names)->mapWithKeys(fn ($name, $i) => [$i + 1 => $name])->all();

        return new DeviceModelMatcher($models, $prefix, $brandWords, $aliases);
    }

    private function samsung(): DeviceModelMatcher
    {
        return $this->matcher(self::GALAXIES, 'Galaxy', ['samsung'], ['/\bZ (Fold|Flip)\b/i' => '$1']);
    }

    private function assertMatches(?string $expected, string $title, ?DeviceModelMatcher $matcher = null): void
    {
        $matcher ??= $this->matcher();
        $id = $matcher->match($title);
        $models = (fn () => $this->models)->call($matcher);

        $this->assertSame($expected, $id === null ? null : $models[$id], "Title: {$title}");
    }

    public function test_matches_plain_model_names(): void
    {
        $this->assertMatches('iPhone 11', 'Clean iPhone 11 for sale');
        $this->assertMatches('iPhone XR', 'UK used iPhone XR 64gb');
        $this->assertMatches('iPhone 16e', 'Brand new iPhone 16e');
    }

    public function test_prefers_the_most_specific_variant(): void
    {
        $this->assertMatches('iPhone 12 Pro Max', 'iPhone 12 Pro Max 256gb blue');
        $this->assertMatches('iPhone XS Max', 'Apple iPhone XS Max');
        $this->assertMatches('iPhone 12 mini', 'iphone 12 mini');
    }

    public function test_tolerates_squashed_spelling(): void
    {
        $this->assertMatches('iPhone 12 Pro Max', 'iphone 12promax');
        $this->assertMatches('iPhone 11 Pro Max', 'iPhone11 pro-max');
        $this->assertMatches('iPhone XS Max', 'iphone xsmax');
        $this->assertMatches('iPhone 7 Plus', 'iphone 7plus');
    }

    public function test_title_may_start_with_a_numeric_model_without_the_iphone_prefix(): void
    {
        $this->assertMatches('iPhone 11 Pro Max', '11 promax || 64gb || 79bh || 335k');
        $this->assertMatches('iPhone 13 Pro Max', '13 pro max 512gb');
        $this->assertMatches('iPhone 17 Pro Max', 'Apple 17 pro max');
    }

    public function test_bare_numbers_mid_title_are_not_models(): void
    {
        $this->assertMatches('iPhone 13 Pro Max', 'iPhone 13 pro max, used 6 months');
        $this->assertMatches(null, 'Phone for sale, 6 months old');
    }

    public function test_letter_models_need_the_iphone_prefix(): void
    {
        $this->assertMatches(null, 'X');
        $this->assertMatches(null, 'Clean phone, x 2 available');
        $this->assertMatches('iPhone X', 'iPhone X 256gb');
    }

    public function test_title_may_start_with_a_two_letter_model_without_the_iphone_prefix(): void
    {
        $this->assertMatches('iPhone XS Max', 'Xs max 64gb');
        $this->assertMatches('iPhone XR', 'XR 128gb clean');
        // ...but a range of models is still ambiguous.
        $this->assertMatches(null, 'Xr to 17 pro max');
    }

    public function test_air_is_a_variant_word(): void
    {
        $this->assertMatches(null, 'Iphone 17 Air skyblue 256gb');
    }

    public function test_unknown_variant_is_not_downgraded_to_the_base_model(): void
    {
        $this->assertMatches(null, 'iPhone 12 prom max 256 gig blue');
        // "12 max" doesn't exist — don't silently file it under iPhone 12.
        $this->assertMatches(null, 'iphone 12 max');
        // iPhone 18 isn't in the catalog at all.
        $this->assertMatches(null, 'iphone 18 pro max');
    }

    public function test_titles_naming_several_models_are_ambiguous(): void
    {
        $this->assertMatches(null, 'iPhone 11 and iPhone 12 available');
        $this->assertMatches(null, 'iPhone 12 / iPhone 12 pro');
    }

    public function test_repeating_the_same_model_is_not_ambiguous(): void
    {
        $this->assertMatches('iPhone 11', 'iPhone 11 - neat iphone 11');
    }

    public function test_non_phone_titles_match_nothing(): void
    {
        $this->assertMatches(null, 'Dealer on all kinds of phones');
        $this->assertMatches(null, '2022 my mac book pro');
    }

    public function test_samsung_series_word_may_follow_the_brand_name(): void
    {
        $m = $this->samsung();
        $this->assertMatches('Galaxy A14', 'Samsung A14 5g', $m);
        $this->assertMatches('Galaxy A16', 'SamsungA16', $m);
        $this->assertMatches('Galaxy A06', 'Used Samsung Galaxy A06 for sell', $m);
        $this->assertMatches('Galaxy S21 Ultra', 'Selling Samsung S21 ULTRA 5G', $m);
        $this->assertMatches('Galaxy S8', 'S8 for sale', $m);
        $this->assertMatches('Galaxy Note 10', 'Samsung note 10 256GB', $m);
    }

    public function test_samsung_plus_sign_and_squashed_suffixes(): void
    {
        $m = $this->samsung();
        $this->assertMatches('Galaxy S10 Plus', 'Samsung galaxy S10+', $m);
        $this->assertMatches('Galaxy S10e', 'Samsung Galaxy s10e', $m);
        $this->assertMatches('Galaxy A05s', 'Samsung Galaxy A05s', $m);
        $this->assertMatches('Galaxy S20 FE', 'Samsung galaxy S20FE 5G 128', $m);
        $this->assertMatches('Galaxy S7 Edge', 'Samsung Galaxy S7EDGE', $m);
        $this->assertMatches('Galaxy Note 10 Lite', 'Samsung Galaxy Note 10 Lite', $m);
    }

    public function test_samsung_aliases_and_unknown_variants(): void
    {
        $m = $this->samsung();
        $this->assertMatches('Galaxy Z Fold 4', 'Samsung galaxy fold 4', $m);
        $this->assertMatches('Galaxy Z Flip 4', 'Galaxy Z flip4', $m);
        $this->assertMatches(null, 'Samsung A25ultra just 2 months old', $m);
        $this->assertMatches(null, 'Samsung A06 and A07', $m);
        $this->assertMatches(null, 'UK used clean samsung 5G', $m);
        $this->assertMatches(null, 'Samsung 10e 128gb', $m);
    }

    public function test_brands_without_a_series_word_need_the_full_name(): void
    {
        $tecno = $this->matcher(['Camon 40', 'Camon 40 Pro', 'Spark 40', 'Spark 40 Pro', 'Spark 40 Pro Plus', 'Pop 10', 'Pop 10 Pro', 'Pova Neo'], null, ['tecno']);
        $this->assertMatches('Camon 40 Pro', 'Clean Tecno camon 40 pro 256gb', $tecno);
        $this->assertMatches('Camon 40', 'Tecno camon40', $tecno);
        $this->assertMatches('Spark 40 Pro Plus', 'Tecno Spark 40pro plus', $tecno);
        $this->assertMatches('Pop 10', 'POP 10', $tecno);
        $this->assertMatches('Pova Neo', 'Tecno Pova Neo for sale', $tecno);
        $this->assertMatches(null, '40 pieces available', $tecno);
        $this->assertMatches(null, 'Tecno Camon 40i for sale', $tecno);

        $infinix = $this->matcher(['Hot 40', 'Hot 40i', 'Hot 40 Pro', 'Smart 9', 'Smart 9 HD', 'Hot 10 Play'], null, ['infinix']);
        $this->assertMatches('Hot 40i', 'Infinix Hot 40 I', $infinix);
        $this->assertMatches('Smart 9 HD', 'Infinix smart 9 HD 64GB', $infinix);
        $this->assertMatches(null, 'Infinix hot 11 play', $infinix);
        $this->assertMatches('Hot 10 Play', 'Infinix Hot 10 Play', $infinix);

        $xiaomi = $this->matcher(['Redmi 13', 'Redmi 13C', 'Redmi A3', 'Redmi A3x', 'Xiaomi 13', 'Xiaomi 13T Pro', 'Redmi Note 14', 'Redmi Note 14 Pro'], null, ['xiaomi']);
        $this->assertMatches('Redmi 13C', 'Xiaomi Redmi 13c 256GB', $xiaomi);
        $this->assertMatches('Redmi A3x', 'Redmi a3x 64gb', $xiaomi);
        $this->assertMatches('Xiaomi 13', 'Xiaomi 13 12GB 512GB', $xiaomi);
        $this->assertMatches('Xiaomi 13T Pro', 'Xiaomi 13T PRO', $xiaomi);
        $this->assertMatches('Redmi Note 14 Pro', 'Redmi note 14 pro 5G', $xiaomi);
    }
}
