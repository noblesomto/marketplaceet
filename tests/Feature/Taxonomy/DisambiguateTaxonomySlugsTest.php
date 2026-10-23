<?php

namespace Tests\Feature\Taxonomy;

use App\Models\Brands;
use App\Models\SubCategory;
use App\Models\VehicleModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DisambiguateTaxonomySlugsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_command_reslugs_legacy_numeric_suffixed_row_and_records_redirect(): void
    {
        $subcats = SubCategory::take(2)->get();

        if ($subcats->count() < 2) {
            $this->markTestSkipped('Need at least 2 subcategories in database.');
        }

        [$first, $second] = $subcats;
        $uniqueName = 'Zz Disambig Test Brand ' . uniqid();
        $baseSlug = Str::slug($uniqueName);
        $legacySlug = $baseSlug . '-2';

        Brands::create(['subcat_id' => $first->id, 'brand' => $uniqueName]);
        $brandTwo = Brands::create(['subcat_id' => $second->id, 'brand' => $uniqueName]);

        // Task 2's disambiguation logic is already active, so brandTwo just
        // received a parent-qualified slug, not a numeric one. Force it back
        // to the legacy numeric form directly via the DB, bypassing the
        // model's own sluggable logic, to simulate "existing legacy data" as
        // it looked before this branch's fix — this is exactly what the
        // backfill command exists to repair.
        DB::table('brands')->where('id', $brandTwo->id)->update(['brand_slug' => $legacySlug]);

        // The command gates the real run behind $this->confirm(); compute the
        // same total it will report so we can answer the exact confirmation
        // prompt via Laravel's console-testing helper (plain Artisan::call()
        // can't drive an interactive prompt — see
        // test_real_run_declines_without_confirmation for what happens then).
        $total = SubCategory::whereRaw("sub_cat_slug REGEXP '-[0-9]+$'")->count()
            + Brands::whereRaw("brand_slug REGEXP '-[0-9]+$'")->count()
            + VehicleModel::whereRaw("model_slug REGEXP '-[0-9]+$'")->count();

        $this->artisan('taxonomy:disambiguate-slugs')
            ->expectsConfirmation("This will rename {$total} rows and cannot be easily undone. Continue?", 'yes')
            ->assertExitCode(0);

        $brandTwo->refresh();

        $expectedNewSlug = $baseSlug . '-' . $second->sub_cat_slug;
        $this->assertSame($expectedNewSlug, $brandTwo->brand_slug);

        $redirect = DB::table('slug_redirects')
            ->where('sluggable_type', 'brand')
            ->where('old_slug', $legacySlug)
            ->first();

        $this->assertNotNull($redirect);
        $this->assertSame($expectedNewSlug, $redirect->new_slug);
    }

    public function test_dry_run_leaves_database_unchanged(): void
    {
        $subcats = SubCategory::take(2)->get();

        if ($subcats->count() < 2) {
            $this->markTestSkipped('Need at least 2 subcategories in database.');
        }

        [$first, $second] = $subcats;
        $uniqueName = 'Zz Disambig Dryrun Brand ' . uniqid();
        $baseSlug = Str::slug($uniqueName);
        $legacySlug = $baseSlug . '-2';

        Brands::create(['subcat_id' => $first->id, 'brand' => $uniqueName]);
        $brandTwo = Brands::create(['subcat_id' => $second->id, 'brand' => $uniqueName]);

        DB::table('brands')->where('id', $brandTwo->id)->update(['brand_slug' => $legacySlug]);

        $slugBefore = DB::table('brands')->where('id', $brandTwo->id)->value('brand_slug');
        $redirectCountBefore = DB::table('slug_redirects')->count();

        Artisan::call('taxonomy:disambiguate-slugs', ['--dry-run' => true]);

        $slugAfter = DB::table('brands')->where('id', $brandTwo->id)->value('brand_slug');
        $redirectCountAfter = DB::table('slug_redirects')->count();

        $this->assertSame($slugBefore, $slugAfter, 'Dry-run must not persist any slug change.');
        $this->assertSame($legacySlug, $slugAfter, 'Sanity check: the legacy slug must still be in place.');
        $this->assertSame($redirectCountBefore, $redirectCountAfter, 'Dry-run must not write any slug_redirects rows.');
    }

    public function test_real_run_declines_without_confirmation(): void
    {
        $subcats = SubCategory::take(2)->get();

        if ($subcats->count() < 2) {
            $this->markTestSkipped('Need at least 2 subcategories in database.');
        }

        [$first, $second] = $subcats;
        $uniqueName = 'Zz Disambig Noconfirm Brand ' . uniqid();
        $baseSlug = Str::slug($uniqueName);
        $legacySlug = $baseSlug . '-2';

        Brands::create(['subcat_id' => $first->id, 'brand' => $uniqueName]);
        $brandTwo = Brands::create(['subcat_id' => $second->id, 'brand' => $uniqueName]);

        DB::table('brands')->where('id', $brandTwo->id)->update(['brand_slug' => $legacySlug]);

        // No --no-interaction flag: confirm() falls back to its default
        // (false) under Artisan::call's non-interactive input, so the
        // command must decline and leave the row untouched.
        $exitCode = Artisan::call('taxonomy:disambiguate-slugs');

        $slugAfter = DB::table('brands')->where('id', $brandTwo->id)->value('brand_slug');

        $this->assertSame(0, $exitCode);
        $this->assertSame($legacySlug, $slugAfter);
    }

    public function test_force_option_skips_confirmation_and_actually_runs(): void
    {
        $subcats = SubCategory::take(2)->get();

        if ($subcats->count() < 2) {
            $this->markTestSkipped('Need at least 2 subcategories in database.');
        }

        [$first, $second] = $subcats;
        $uniqueName = 'Zz Disambig Force Brand ' . uniqid();
        $baseSlug = Str::slug($uniqueName);
        $legacySlug = $baseSlug . '-2';

        Brands::create(['subcat_id' => $first->id, 'brand' => $uniqueName]);
        $brandTwo = Brands::create(['subcat_id' => $second->id, 'brand' => $uniqueName]);

        DB::table('brands')->where('id', $brandTwo->id)->update(['brand_slug' => $legacySlug]);

        // Artisan::call is a genuinely non-interactive invocation (no
        // expectsConfirmation stub involved) — this proves --force bypasses
        // the prompt for real, not just in a mocked-confirmation test.
        $exitCode = Artisan::call('taxonomy:disambiguate-slugs', ['--force' => true]);

        $brandTwo->refresh();

        $expectedNewSlug = $baseSlug . '-' . $second->sub_cat_slug;

        $this->assertSame(0, $exitCode);
        $this->assertSame($expectedNewSlug, $brandTwo->brand_slug);

        $redirect = DB::table('slug_redirects')
            ->where('sluggable_type', 'brand')
            ->where('old_slug', $legacySlug)
            ->first();

        $this->assertNotNull($redirect);
        $this->assertSame($expectedNewSlug, $redirect->new_slug);
    }
}
