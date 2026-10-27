<?php

namespace Tests\Unit\Traits;

use App\Traits\GeneratesSeoMeta;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Listing-page meta descriptions stay within Google's 160 characters.
 */
class GeneratesSeoMetaTest extends TestCase
{
    private function seo(): object
    {
        return new class {
            use GeneratesSeoMeta;

            public function __call($method, $args)
            {
                return $this->$method(...$args);
            }
        };
    }

    public static function pages(): array
    {
        return [
            'phone model'       => ['Apple iPhone 14 Pro Max', 'product', 'Ethiopia'],
            'long name and LGA' => ['Children Party Packs Baby & Kids Accessories', 'product', 'Nifas Silk-Lafto, Addis Ababa'],
            'property'          => ['Houses & Apartments For Rent', 'property', 'Bole, Addis Ababa'],
            'service'           => ['Party, Catering & Event Services', 'service', 'Addis Ababa'],
        ];
    }

    #[DataProvider('pages')]
    public function test_descriptions_fit_in_160_characters(string $name, string $group, string $location): void
    {
        $desc = $this->seo()->buildSeoMeta($name, $group, '/', $location, 100)['seoDesc'];

        $this->assertLessThanOrEqual(160, mb_strlen($desc));
        $this->assertStringContainsString($name, $desc);
    }

    public function test_short_names_keep_both_sentences(): void
    {
        $this->assertSame(
            'Buy new and used Apple iPhone 14 Pro Max in Ethiopia on ' . config('global.site_name') . '. Compare prices, chat with verified sellers and pay safely with Buyer Protection.',
            $this->seo()->buildSeoMeta('Apple iPhone 14 Pro Max', 'product', '/', 'Ethiopia', 5)['seoDesc']
        );
    }

    public function test_sentences_that_dont_fit_are_dropped_and_an_overlong_one_is_cut_at_a_word(): void
    {
        $seo = $this->seo();

        $this->assertSame('First sentence.', $seo->fitMetaDescription(['First sentence.', str_repeat('x', 160)]));

        $cut = $seo->fitMetaDescription([str_repeat('word ', 50)]);
        $this->assertLessThanOrEqual(160, mb_strlen($cut));
        $this->assertStringEndsWith('word…', $cut);
    }
}
