<?php

namespace Tests\Unit\Support;

use App\Support\TaxonomySlugDisambiguator;
use Tests\TestCase;

class TaxonomySlugDisambiguatorTest extends TestCase
{
    public function test_uses_parent_slug_when_available(): void
    {
        $existing = collect(['apple']);

        $suffix = TaxonomySlugDisambiguator::suffix('apple', '-', $existing, 2, 'tablets');

        $this->assertSame('tablets', $suffix);
    }

    public function test_falls_back_to_numeric_when_no_parent_slug(): void
    {
        $existing = collect(['apple']);

        $suffix = TaxonomySlugDisambiguator::suffix('apple', '-', $existing, 2, null);

        $this->assertSame('2', $suffix);
    }

    public function test_falls_back_to_numeric_when_parent_slug_is_empty_string(): void
    {
        $existing = collect(['apple']);

        $suffix = TaxonomySlugDisambiguator::suffix('apple', '-', $existing, 2, '');

        $this->assertSame('2', $suffix);
    }

    public function test_appends_number_after_parent_slug_when_that_is_also_taken(): void
    {
        $existing = collect(['apple', 'apple-tablets']);

        $suffix = TaxonomySlugDisambiguator::suffix('apple', '-', $existing, 2, 'tablets');

        $this->assertSame('tablets-2', $suffix);
    }

    public function test_increments_past_multiple_taken_parent_qualified_slugs(): void
    {
        $existing = collect(['apple', 'apple-tablets', 'apple-tablets-2']);

        $suffix = TaxonomySlugDisambiguator::suffix('apple', '-', $existing, 2, 'tablets');

        $this->assertSame('tablets-3', $suffix);
    }

    public function test_numeric_fallback_matches_package_default_behaviour(): void
    {
        $existing = collect(['apple', 'apple-2', 'apple-3']);

        $suffix = TaxonomySlugDisambiguator::suffix('apple', '-', $existing, 2, null);

        $this->assertSame('4', $suffix);
    }

    public function test_numeric_fallback_ignores_unrelated_slugs_in_the_list(): void
    {
        // "applesauce" starts with "apple" as a raw string but not as
        // "apple-" — must not be mistaken for a numbered collision.
        $existing = collect(['apple', 'applesauce']);

        $suffix = TaxonomySlugDisambiguator::suffix('apple', '-', $existing, 2, null);

        $this->assertSame('2', $suffix);
    }
}
