<?php

namespace Tests\Feature\Taxonomy;

use App\Models\Brands;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\VehicleModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MergeBrandsTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAdvert(int $brandId, SubCategory $subcat): int
    {
        $user = User::factory()->create();

        return DB::table('adverts')->insertGetId([
            'user_id' => $user->user_id,
            'ad_type' => 'Sell',
            'title_slug' => 'zz-merge-brands-test-' . uniqid(),
            'category' => (string) $subcat->cat_id,
            'sub_category' => (string) $subcat->id,
            'brand' => (string) $brandId,
            'buy_direct' => 'No',
            'description' => 'test advert for MergeBrandsTest',
            'state' => 'Addis Ababa',
            'lga' => 'Bole',
            'state_slug' => 'ikeja',
            'ad_status' => 'active',
            'views' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeBrandTrio(): array
    {
        $subcat = SubCategory::whereNotNull('cat_id')->first();
        $this->assertNotNull($subcat, 'Need at least 1 subcategory in database.');

        $suffix = uniqid();
        $keep = Brands::create(['subcat_id' => $subcat->id, 'brand' => "Zz Keep Brand {$suffix}"]);
        $mergeOne = Brands::create(['subcat_id' => $subcat->id, 'brand' => "Zz Merge One {$suffix}"]);
        $mergeTwo = Brands::create(['subcat_id' => $subcat->id, 'brand' => "Zz Merge Two {$suffix}"]);

        return [$keep, $mergeOne, $mergeTwo, $subcat];
    }

    public function test_command_reassigns_adverts_and_models_records_redirects_and_deletes_merged_rows(): void
    {
        [$keep, $mergeOne, $mergeTwo, $subcat] = $this->makeBrandTrio();

        $advertOnKeep = $this->makeAdvert($keep->id, $subcat);
        $advertOnMergeOne = $this->makeAdvert($mergeOne->id, $subcat);
        $advertOnMergeTwo = $this->makeAdvert($mergeTwo->id, $subcat);

        $model = VehicleModel::create(['subcat_id' => $subcat->id, 'brand_id' => $mergeOne->id, 'model' => 'Zz Merge Model ' . uniqid()]);

        $mergeOneSlug = $mergeOne->brand_slug;
        $mergeTwoSlug = $mergeTwo->brand_slug;
        $keepOldSlug = $keep->brand_slug;

        $this->artisan('taxonomy:merge-brands', [
            'keep' => (string) $keep->id,
            'merge' => [(string) $mergeOne->id, (string) $mergeTwo->id],
            '--name' => 'PlayStation ' . uniqid(),
        ])
            ->expectsConfirmation('Apply this merge? It cannot be easily undone. Continue?', 'yes')
            ->assertExitCode(0);

        $keep->refresh();

        // Adverts previously on the merged brands now point at the kept brand;
        // the advert already on the kept brand is untouched.
        $this->assertSame((string) $keep->id, DB::table('adverts')->where('id', $advertOnKeep)->value('brand'));
        $this->assertSame((string) $keep->id, DB::table('adverts')->where('id', $advertOnMergeOne)->value('brand'));
        $this->assertSame((string) $keep->id, DB::table('adverts')->where('id', $advertOnMergeTwo)->value('brand'));

        // Model FK reassigned too.
        $model->refresh();
        $this->assertSame($keep->id, $model->brand_id);

        // Merged rows are gone.
        $this->assertNull(Brands::find($mergeOne->id));
        $this->assertNull(Brands::find($mergeTwo->id));

        // Redirects recorded for both merged brands' old slugs and the kept
        // brand's own pre-rename slug, all pointing at its final new slug.
        foreach ([$mergeOneSlug, $mergeTwoSlug, $keepOldSlug] as $oldSlug) {
            $redirect = DB::table('slug_redirects')
                ->where('sluggable_type', 'brand')
                ->where('old_slug', $oldSlug)
                ->first();

            $this->assertNotNull($redirect, "Expected a redirect recorded for old slug: {$oldSlug}");
            $this->assertSame($keep->brand_slug, $redirect->new_slug);
        }
    }

    public function test_dry_run_leaves_database_unchanged(): void
    {
        [$keep, $mergeOne, , $subcat] = $this->makeBrandTrio();
        $advertOnMergeOne = $this->makeAdvert($mergeOne->id, $subcat);

        $redirectCountBefore = DB::table('slug_redirects')->count();

        Artisan::call('taxonomy:merge-brands', [
            'keep' => (string) $keep->id,
            'merge' => [(string) $mergeOne->id],
            '--dry-run' => true,
        ]);

        $this->assertNotNull(Brands::find($mergeOne->id), 'Dry-run must not delete the merged brand.');
        $this->assertSame((string) $mergeOne->id, DB::table('adverts')->where('id', $advertOnMergeOne)->value('brand'));
        $this->assertSame($redirectCountBefore, DB::table('slug_redirects')->count());
    }

    public function test_real_run_declines_without_confirmation(): void
    {
        [$keep, $mergeOne] = $this->makeBrandTrio();

        // No --no-interaction flag: confirm() falls back to its default
        // (false) under Artisan::call's non-interactive input, so the
        // command must decline and leave the rows untouched.
        $exitCode = Artisan::call('taxonomy:merge-brands', [
            'keep' => (string) $keep->id,
            'merge' => [(string) $mergeOne->id],
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertNotNull(Brands::find($mergeOne->id));
    }

    public function test_force_option_skips_confirmation_and_actually_runs(): void
    {
        [$keep, $mergeOne, , $subcat] = $this->makeBrandTrio();
        $advertOnMergeOne = $this->makeAdvert($mergeOne->id, $subcat);

        // Artisan::call is a genuinely non-interactive invocation (no
        // expectsConfirmation stub involved) — proves --force bypasses the
        // prompt for real, not just in a mocked-confirmation test.
        $exitCode = Artisan::call('taxonomy:merge-brands', [
            'keep' => (string) $keep->id,
            'merge' => [(string) $mergeOne->id],
            '--force' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertNull(Brands::find($mergeOne->id));
        $this->assertSame((string) $keep->id, DB::table('adverts')->where('id', $advertOnMergeOne)->value('brand'));
    }

    public function test_fails_when_a_brand_reference_does_not_resolve(): void
    {
        [$keep] = $this->makeBrandTrio();

        $exitCode = Artisan::call('taxonomy:merge-brands', [
            'keep' => (string) $keep->id,
            'merge' => ['this-slug-does-not-exist-' . uniqid()],
        ]);

        $this->assertSame(1, $exitCode);
    }
}
