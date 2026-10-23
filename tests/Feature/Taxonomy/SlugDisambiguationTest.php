<?php

namespace Tests\Feature\Taxonomy;

use App\Models\Brands;
use App\Models\SubCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class SlugDisambiguationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_colliding_brand_name_gets_subcategory_qualified_slug(): void
    {
        $subcats = SubCategory::take(2)->get();

        if ($subcats->count() < 2) {
            $this->markTestSkipped('Need at least 2 subcategories in database.');
        }

        [$first, $second] = $subcats;
        $uniqueName = 'Zz Test Brand ' . uniqid();

        $brandOne = Brands::create(['subcat_id' => $first->id, 'brand' => $uniqueName]);
        $brandTwo = Brands::create(['subcat_id' => $second->id, 'brand' => $uniqueName]);

        $this->assertSame(Str::slug($uniqueName), $brandOne->brand_slug);
        $this->assertSame(Str::slug($uniqueName) . '-' . $second->sub_cat_slug, $brandTwo->brand_slug);
        $this->assertDoesNotMatchRegularExpression('/-\d+$/', $brandTwo->brand_slug);
    }

    public function test_colliding_subcategory_name_gets_category_qualified_slug(): void
    {
        $cats = \App\Models\Category::take(2)->get();

        if ($cats->count() < 2) {
            $this->markTestSkipped('Need at least 2 categories in database.');
        }

        [$first, $second] = $cats;
        $uniqueName = 'Zz Test Subcat ' . uniqid();

        $subOne = SubCategory::create(['cat_id' => $first->id, 'sub_category' => $uniqueName]);
        $subTwo = SubCategory::create(['cat_id' => $second->id, 'sub_category' => $uniqueName]);

        $this->assertSame(Str::slug($uniqueName), $subOne->sub_cat_slug);
        $this->assertSame(Str::slug($uniqueName) . '-' . $second->category_slug, $subTwo->sub_cat_slug);
        $this->assertDoesNotMatchRegularExpression('/-\d+$/', $subTwo->sub_cat_slug);
    }
}
