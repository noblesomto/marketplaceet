<?php

namespace Tests\Feature\Taxonomy;

use App\Models\Brands;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LocalizeSeoTextTest extends TestCase
{
    use DatabaseTransactions;

    private function makeBrand(array $seo): Brands
    {
        $brand = Brands::first();

        if (!$brand) {
            $this->markTestSkipped('No brands in database.');
        }

        $brand->forceFill($seo)->save();

        return $brand;
    }

    public function test_rewrites_nigeria_seo_text_for_ethiopia(): void
    {
        $brand = $this->makeBrand([
            'keywords' => 'toyota cars nigeria, tokunbo toyota, buy toyota Lagos, marketplace ng toyota',
            'meta_title' => 'Toyota Cars for Sale in Nigeria | Marketplace.ng',
            'meta_description' => 'Buy and sell Toyota cars in Nigeria. Find new, foreign used, and tokunbo models in Lagos, Abuja and more on Marketplace.ng',
        ]);

        $this->artisan('seo:localize-ethiopia')->assertSuccessful();

        $brand->refresh();
        $this->assertSame('toyota cars ethiopia, foreign used toyota, buy toyota Addis Ababa, marketplace ethiopia toyota', $brand->keywords);
        $this->assertSame('Toyota Cars for Sale in Ethiopia | Marketplace Ethiopia', $brand->meta_title);
        $this->assertSame('Buy and sell Toyota cars in Ethiopia. Find new, foreign used models in Addis Ababa, Adama and more on Marketplace Ethiopia', $brand->meta_description);
    }

    public function test_dry_run_changes_nothing(): void
    {
        $brand = $this->makeBrand(['meta_title' => 'Toyota Cars for Sale in Nigeria | Marketplace.ng']);

        $this->artisan('seo:localize-ethiopia', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame('Toyota Cars for Sale in Nigeria | Marketplace.ng', $brand->refresh()->meta_title);
    }

    public function test_drops_trailing_site_name_when_description_would_exceed_160_chars(): void
    {
        $original = 'Buy and sell Toyota Land Cruiser cars in Nigeria. Browse new and used models, compare prices, check features and connect with verified sellers on Marketplace.ng';
        $this->assertSame(160, mb_strlen($original));

        $brand = $this->makeBrand(['meta_description' => $original]);

        $this->artisan('seo:localize-ethiopia')->assertSuccessful();

        $description = $brand->refresh()->meta_description;
        $this->assertLessThanOrEqual(160, mb_strlen($description));
        $this->assertStringEndsWith('connect with verified sellers.', $description);
    }
}
