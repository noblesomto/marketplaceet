<?php

namespace Tests\Feature;

use App\Models\Advert;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * A new advert's ad_id is its id, unless an older advert (with a random
 * 5-digit ad_id) already holds that number.
 */
class AdvertAdIdTest extends TestCase
{
    use DatabaseTransactions;

    private function newAdvert(): Advert
    {
        $template = Advert::first() ?? $this->markTestSkipped('Needs an advert.');
        $advert = $template->replicate(['title_slug']);
        $advert->ad_id = 'tmp-' . uniqid();
        $advert->title_slug = Str::slug('ad id test ' . uniqid());
        $advert->save();

        return $advert;
    }

    public function test_ad_id_is_the_advert_id(): void
    {
        $advert = $this->newAdvert();
        $advert->assignAdId();

        $this->assertSame((string) $advert->id, $advert->fresh()->ad_id);
    }

    public function test_a_taken_id_gets_the_next_free_ad_id(): void
    {
        $advert = $this->newAdvert();
        $older = $this->newAdvert();
        $older->update(['ad_id' => (string) $advert->id]);

        $advert->assignAdId();

        $highest = (int) DB::table('adverts')->where('id', '!=', $advert->id)->max(DB::raw('CAST(ad_id AS UNSIGNED)'));
        $this->assertSame((string) ($highest + 1), $advert->fresh()->ad_id);
    }

    public function test_new_ids_start_past_existing_ad_ids(): void
    {
        $highest = (int) DB::table('adverts')->max(DB::raw('CAST(ad_id AS UNSIGNED)'));

        $this->assertGreaterThan($highest, $this->newAdvert()->id);
    }
}
