# Taxonomy Slug Disambiguation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stop `SubCategory`, `Brands`, and `VehicleModel` slugs from colliding into ugly numeric suffixes (`other-14`, `apple-2`, `contract-9`) by making EloquentSluggable's collision suffix parent-context-qualified (`other-fragrances`, `apple-tablets`) instead of an opaque counter — for the ~1,300+ existing rows and for every future collision — while preserving 301 redirects for already-indexed URLs.

**Architecture:** A pure, unit-testable `TaxonomySlugDisambiguator::suffix()` helper replaces EloquentSluggable's default numeric `uniqueSuffix` on all three affected models, using each model's existing parent relation (`Brands->subCategory`, `SubCategory->category`, `VehicleModel->brand`) as the qualifier. A one-time artisan command force-reslugs existing colliding rows and records `old_slug -> new_slug` pairs in a new `slug_redirects` table. Four existing "look up by slug, 404 if not found" call sites (2 in `SearchFilter`, 2 in `AdvertController`) gain a fallback: on a failed lookup, check `slug_redirects` and 301 to the new URL before giving up to a real 404.

**Tech Stack:** Laravel 12, `cviebrock/eloquent-sluggable` (already in use, config left at package defaults except for the three models' `uniqueSuffix`), PHPUnit (existing suite, MySQL/MariaDB-backed, no RefreshDatabase/sqlite/factories in use today).

## Global Constraints

- This repo's test suite runs against the real configured database — no `RefreshDatabase`, no sqlite in-memory, no model factories exist for `Brands`/`SubCategory`/`VehicleModel`. Existing tests are read-only (find-or-skip pattern). Any new test in this plan that must write rows uses `Illuminate\Foundation\Testing\DatabaseTransactions` (auto-rollback per test) — do not add `RefreshDatabase` or factories.
- Follow the existing constructor-promoted-property DI style already used in `SearchFilter` and `AdvertController` (`private ServiceType $name` in `__construct`).
- DB engine is MySQL/MariaDB — `REGEXP` in `whereRaw` is confirmed to work against this project's DB (already used for investigation queries).
- Never run the backfill command against real data (i.e. without `--dry-run`) without an explicit checkpoint/confirmation — it force-reslugs ~1,300 live rows referenced by indexed URLs. Building, testing, and dry-running it is safe and part of normal task execution; the real run is a separate, flagged step.
- No `git push` and no deploy to `public_html/` without explicit request, per this project's existing conventions.

---

## File Structure

| File | Responsibility |
|---|---|
| `app/Support/TaxonomySlugDisambiguator.php` (new) | Pure algorithm: given a colliding base slug and a parent slug, return the disambiguating suffix. No DB, no Eloquent — fully unit-testable. |
| `app/Models/Brands.php` (modify) | Wire `uniqueSuffix` to the disambiguator using `subCategory->sub_cat_slug`. |
| `app/Models/SubCategory.php` (modify) | Wire `uniqueSuffix` to the disambiguator using `category->category_slug`. |
| `app/Models/VehicleModel.php` (modify) | Wire `uniqueSuffix` to the disambiguator using `brand->brand_slug`. |
| `database/migrations/2026_08_05_000001_create_slug_redirects_table.php` (new) | `slug_redirects` table: `sluggable_type`, `old_slug`, `new_slug`. |
| `app/Services/SlugRedirectResolver.php` (new) | `resolve()` (route lookups) and `record()` (backfill command) against `slug_redirects`. |
| `app/Console/Commands/DisambiguateTaxonomySlugs.php` (new) | One-time backfill: force-reslug every row whose slug matches `-[0-9]+$`, record the redirect. |
| `app/Http/Controllers/Shop/SearchFilter.php` (modify) | `location_router()` and `location_category_brand()` fall back to a redirect before `abort(404)`. |
| `app/Http/Controllers/User/AdvertController.php` (modify) | `sub_category()` and `brand()` fall back to a redirect before `abort(404)`. |

---

### Task 1: Pure slug-disambiguation algorithm

**Files:**
- Create: `app/Support/TaxonomySlugDisambiguator.php`
- Test: `tests/Unit/Support/TaxonomySlugDisambiguatorTest.php`

**Interfaces:**
- Produces: `TaxonomySlugDisambiguator::suffix(string $slug, string $separator, Collection $existingSlugs, $firstSuffix, ?string $parentSlug): string` — static method. `$existingSlugs` is a plain `Collection` of slug strings already in use (values only matter, matching how EloquentSluggable's `uniqueSuffix` closure receives its `$list` argument). Returns just the suffix to append after `$slug . $separator`, never the full slug.

- [ ] **Step 1: Write the failing tests**

```php
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
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Unit/Support/TaxonomySlugDisambiguatorTest.php`
Expected: FAIL — `Class "App\Support\TaxonomySlugDisambiguator" not found`.

- [ ] **Step 3: Write the implementation**

```php
<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Computes the suffix EloquentSluggable appends when a slug collides.
 *
 * Given a parent slug (e.g. the owning subcategory's slug), prefers a
 * descriptive suffix ("apple-tablets") over the package's default numeric
 * counter ("apple-2") — falling back to a numeric suffix only when the
 * parent-qualified slug is itself already taken (a genuine duplicate within
 * the same parent), or when there's no parent slug to qualify with.
 */
class TaxonomySlugDisambiguator
{
    public static function suffix(string $slug, string $separator, Collection $existingSlugs, $firstSuffix, ?string $parentSlug): string
    {
        if ($parentSlug === null || $parentSlug === '') {
            return self::numericSuffix($slug, $separator, $existingSlugs, $firstSuffix);
        }

        $candidate = $slug . $separator . $parentSlug;

        if (!$existingSlugs->contains($candidate)) {
            return $parentSlug;
        }

        $n = $firstSuffix;
        while ($existingSlugs->contains($candidate . $separator . $n)) {
            $n++;
        }

        return $parentSlug . $separator . $n;
    }

    private static function numericSuffix(string $slug, string $separator, Collection $existingSlugs, $firstSuffix): string
    {
        $prefix = $slug . $separator;
        $len = strlen($prefix);

        $max = $existingSlugs
            ->filter(fn ($value) => is_string($value) && str_starts_with($value, $prefix))
            ->map(fn ($value) => (int) substr($value, $len))
            ->push(0)
            ->max();

        return (string) ($max === 0 ? $firstSuffix : $max + 1);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Unit/Support/TaxonomySlugDisambiguatorTest.php`
Expected: PASS (7 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Support/TaxonomySlugDisambiguator.php tests/Unit/Support/TaxonomySlugDisambiguatorTest.php
git commit -m "$(cat <<'EOF'
Add pure suffix-disambiguation algorithm for colliding taxonomy slugs

Parent-context-qualified suffixes (apple-tablets) read better and carry
SEO signal, unlike EloquentSluggable's default opaque counter (apple-2).
Kept as a standalone, DB-free class so the collision algorithm itself is
unit-testable without touching Eloquent relations.
EOF
)"
```

---

### Task 2: Wire the disambiguator into Brands, SubCategory, VehicleModel

**Files:**
- Modify: `app/Models/Brands.php`
- Modify: `app/Models/SubCategory.php`
- Modify: `app/Models/VehicleModel.php`
- Test: `tests/Feature/Taxonomy/SlugDisambiguationTest.php`

**Interfaces:**
- Consumes: `TaxonomySlugDisambiguator::suffix()` from Task 1.
- Produces: no new public API — this task changes *behavior* of existing `Brands::create()` / `SubCategory::create()` / `VehicleModel::create()` when their name collides with an existing row.

- [ ] **Step 1: Write the failing test**

```php
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
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Taxonomy/SlugDisambiguationTest.php`
Expected: FAIL — `$brandTwo->brand_slug` is `..._Str::slug($uniqueName) . '-2'` (package default), not the subcategory-qualified value.

- [ ] **Step 3: Modify `app/Models/Brands.php`**

Change the `sluggable()` method (currently just `'source' => 'brand'`) to:

```php
    public function sluggable(): array
    {
        return [
            'brand_slug' => [
                'source' => 'brand',
                'uniqueSuffix' => fn ($slug, $separator, $list, $firstSuffix) =>
                    TaxonomySlugDisambiguator::suffix($slug, $separator, $list, $firstSuffix, $this->subCategory?->sub_cat_slug),
            ],
        ];
    }
```

Add the import at the top with the other `use` statements:

```php
use App\Support\TaxonomySlugDisambiguator;
```

- [ ] **Step 4: Modify `app/Models/SubCategory.php`**

Change `sluggable()` the same way, keyed on the category relation:

```php
    public function sluggable(): array
    {
        return [
            'sub_cat_slug' => [
                'source' => 'sub_category',
                'uniqueSuffix' => fn ($slug, $separator, $list, $firstSuffix) =>
                    TaxonomySlugDisambiguator::suffix($slug, $separator, $list, $firstSuffix, $this->category?->category_slug),
            ],
        ];
    }
```

Add `use App\Support\TaxonomySlugDisambiguator;` to the imports.

- [ ] **Step 5: Modify `app/Models/VehicleModel.php`**

Same pattern, keyed on the brand relation:

```php
    public function sluggable(): array
    {
        return [
            'model_slug' => [
                'source' => 'model',
                'uniqueSuffix' => fn ($slug, $separator, $list, $firstSuffix) =>
                    TaxonomySlugDisambiguator::suffix($slug, $separator, $list, $firstSuffix, $this->brand?->brand_slug),
            ],
        ];
    }
```

Add `use App\Support\TaxonomySlugDisambiguator;` to the imports.

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test tests/Feature/Taxonomy/SlugDisambiguationTest.php`
Expected: PASS (2 tests). No other rows are left behind — `DatabaseTransactions` rolls back automatically.

- [ ] **Step 7: Run the full existing suite to check for regressions**

Run: `php artisan test`
Expected: PASS — in particular `tests/Feature/Phase7/SearchFilterRoutesTest.php` (`test_subcategory_page_returns_200`, `test_brand_page_returns_200`) must still pass unchanged, since this task doesn't touch routing.

- [ ] **Step 8: Commit**

```bash
git add app/Models/Brands.php app/Models/SubCategory.php app/Models/VehicleModel.php tests/Feature/Taxonomy/SlugDisambiguationTest.php
git commit -m "$(cat <<'EOF'
Give colliding Brands/SubCategory/VehicleModel slugs a parent-qualified suffix

Replaces EloquentSluggable's default numeric uniqueSuffix (apple-2) with
TaxonomySlugDisambiguator so future collisions read as apple-tablets
instead. Existing rows are unaffected until the backfill command runs.
EOF
)"
```

---

### Task 3: `slug_redirects` table and resolver service

**Files:**
- Create: `database/migrations/2026_08_05_000001_create_slug_redirects_table.php`
- Create: `app/Services/SlugRedirectResolver.php`
- Test: `tests/Unit/Services/SlugRedirectResolverTest.php`

**Interfaces:**
- Produces: `SlugRedirectResolver::resolve(string $type, string $oldSlug): ?string` and `SlugRedirectResolver::record(string $type, string $oldSlug, string $newSlug): void`. `$type` is one of `'brand'`, `'subcategory'`, `'model'` (matching the three models from Task 2) — not enforced by an enum, just a plain string, consistent with how `sluggable_type` is stored.

- [ ] **Step 1: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slug_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('sluggable_type'); // 'brand' | 'subcategory' | 'model'
            $table->string('old_slug');
            $table->string('new_slug');
            $table->timestamps();

            $table->unique(['sluggable_type', 'old_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slug_redirects');
    }
};
```

- [ ] **Step 2: Run the migration**

Run: `php artisan migrate --path=database/migrations/2026_08_05_000001_create_slug_redirects_table.php`
Expected: `Migrated: 2026_08_05_000001_create_slug_redirects_table`.

- [ ] **Step 3: Write the failing test**

```php
<?php

namespace Tests\Unit\Services;

use App\Services\SlugRedirectResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SlugRedirectResolverTest extends TestCase
{
    use DatabaseTransactions;

    private SlugRedirectResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new SlugRedirectResolver();
    }

    public function test_resolve_returns_null_when_no_redirect_recorded(): void
    {
        $this->assertNull($this->resolver->resolve('brand', 'zz-does-not-exist'));
    }

    public function test_record_then_resolve_returns_new_slug(): void
    {
        $this->resolver->record('brand', 'zz-other-14', 'zz-other-fragrances');

        $this->assertSame('zz-other-fragrances', $this->resolver->resolve('brand', 'zz-other-14'));
    }

    public function test_record_is_idempotent_for_same_old_slug(): void
    {
        $this->resolver->record('brand', 'zz-apple-2', 'zz-apple-tablets');
        $this->resolver->record('brand', 'zz-apple-2', 'zz-apple-tablets-updated');

        $this->assertSame('zz-apple-tablets-updated', $this->resolver->resolve('brand', 'zz-apple-2'));
    }

    public function test_resolve_is_scoped_by_type(): void
    {
        $this->resolver->record('brand', 'zz-other', 'zz-other-brand-qualified');

        $this->assertNull($this->resolver->resolve('subcategory', 'zz-other'));
    }
}
```

- [ ] **Step 4: Run test to verify it fails**

Run: `php artisan test tests/Unit/Services/SlugRedirectResolverTest.php`
Expected: FAIL — `Class "App\Services\SlugRedirectResolver" not found`.

- [ ] **Step 5: Write the implementation**

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SlugRedirectResolver
{
    public function resolve(string $type, string $oldSlug): ?string
    {
        return DB::table('slug_redirects')
            ->where('sluggable_type', $type)
            ->where('old_slug', $oldSlug)
            ->value('new_slug');
    }

    public function record(string $type, string $oldSlug, string $newSlug): void
    {
        DB::table('slug_redirects')->updateOrInsert(
            ['sluggable_type' => $type, 'old_slug' => $oldSlug],
            ['new_slug' => $newSlug, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test tests/Unit/Services/SlugRedirectResolverTest.php`
Expected: PASS (4 tests).

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_08_05_000001_create_slug_redirects_table.php app/Services/SlugRedirectResolver.php tests/Unit/Services/SlugRedirectResolverTest.php
git commit -m "$(cat <<'EOF'
Add slug_redirects table and resolver for renamed taxonomy slugs

Backing store for 301-redirecting already-indexed URLs (other-14,
apple-2, ...) to their new parent-qualified slugs once the backfill
command renames the underlying rows.
EOF
)"
```

---

### Task 4: Backfill command — re-slug existing collisions

**Files:**
- Create: `app/Console/Commands/DisambiguateTaxonomySlugs.php`

**Interfaces:**
- Consumes: `SlugRedirectResolver::record()` (Task 3), `Cviebrock\EloquentSluggable\Services\SlugService::slug(Model $model, bool $force = true): bool` (package — force-recomputes and sets the slug attribute in-memory without saving), the `uniqueSuffix` wiring from Task 2 (this is what makes the recomputed slug parent-qualified instead of numeric).
- Produces: nothing consumed by later tasks directly — this is a one-time operational script. Its effect (renamed rows + populated `slug_redirects`) is what Task 5/6 redirect logic depends on at runtime.

- [ ] **Step 1: Write the command**

```php
<?php

namespace App\Console\Commands;

use App\Models\Brands;
use App\Models\SubCategory;
use App\Models\VehicleModel;
use App\Services\SlugRedirectResolver;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class DisambiguateTaxonomySlugs extends Command
{
    protected $signature = 'taxonomy:disambiguate-slugs {--dry-run : Preview changes without writing them}';

    protected $description = 'Re-slug SubCategory/Brands/VehicleModel rows whose slug carries a numeric collision suffix (e.g. other-14), using parent-context suffixes instead, and record old->new redirects.';

    public function handle(SlugRedirectResolver $redirects): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $service = new SlugService();

        // Order matters: SubCategory's new slug feeds Brands' suffix source,
        // and Brands' new slug feeds VehicleModel's — process parents first
        // so children pick up the already-renamed parent slug.
        $this->reslug(SubCategory::class, 'sub_cat_slug', 'subcategory', $service, $redirects, $dryRun);
        $this->reslug(Brands::class, 'brand_slug', 'brand', $service, $redirects, $dryRun);
        $this->reslug(VehicleModel::class, 'model_slug', 'model', $service, $redirects, $dryRun);

        return self::SUCCESS;
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function reslug(string $modelClass, string $slugColumn, string $type, SlugService $service, SlugRedirectResolver $redirects, bool $dryRun): void
    {
        $rows = $modelClass::whereRaw("{$slugColumn} REGEXP '-[0-9]+$'")
            ->orderBy('id')
            ->get();

        $this->info("{$type}: {$rows->count()} rows with numeric-suffixed slugs");

        $changed = 0;

        foreach ($rows as $row) {
            $old = $row->{$slugColumn};
            $service->slug($row, true);
            $new = $row->{$slugColumn};

            if ($new === $old) {
                continue;
            }

            $changed++;
            $this->line("  {$old} -> {$new}");

            if (!$dryRun) {
                $row->save();
                $redirects->record($type, $old, $new);
            }
        }

        $this->info("{$type}: {$changed} slugs " . ($dryRun ? 'would change' : 'changed'));
    }
}
```

- [ ] **Step 2: Dry-run against real data and review the output**

Run: `php artisan taxonomy:disambiguate-slugs --dry-run`

Expected: three sections (`subcategory`, `brand`, `model`) each listing `old -> new` pairs, roughly matching the earlier investigation counts (3 subcategory rows, ~1,303 brand rows, ~767 model rows). Read through a sample of the output — confirm `other-14`-style slugs turn into `other-<subcat-slug>`, `apple-2`-style slugs turn into `apple-<subcat-slug>`, and none of the new slugs still end in a bare `-N`.

- [ ] **Step 3: Commit the command (not yet run for real)**

```bash
git add app/Console/Commands/DisambiguateTaxonomySlugs.php
git commit -m "$(cat <<'EOF'
Add one-time backfill command to disambiguate existing collision slugs

--dry-run previews every rename; the real run (checkpoint, not part of
this commit) force-reslugs SubCategory/Brands/VehicleModel rows whose
slug ends in a numeric collision suffix and records old->new mappings
in slug_redirects for the redirect fallback added in the next tasks.
EOF
)"
```

- [ ] **Step 4: CHECKPOINT — do not proceed to the real run without confirmation**

The real run (`php artisan taxonomy:disambiguate-slugs`, no `--dry-run`) mutates ~2,000 live rows referenced by already-indexed URLs. Do not run it for real until Task 5 and Task 6 (the redirect fallback routes) are implemented and tested — otherwise there's a window where old URLs 404 instead of redirecting. Running it for real is scheduled as part of Task 7, after the redirect routes exist.

---

### Task 5: Redirect fallback in `SearchFilter` (flat `/{location}/{slug}` and `/{location}/{category}/{slug}`)

**Files:**
- Modify: `app/Http/Controllers/Shop/SearchFilter.php`
- Test: `tests/Feature/Taxonomy/SlugRedirectRoutesTest.php` (created here, extended in Task 6)

**Interfaces:**
- Consumes: `SlugRedirectResolver::resolve()` (Task 3).

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature\Taxonomy;

use App\Models\Brands;
use App\Services\SlugRedirectResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SlugRedirectRoutesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_flat_location_brand_url_redirects_to_new_slug(): void
    {
        $brand = Brands::whereNotNull('brand_slug')->first();

        if (!$brand) {
            $this->markTestSkipped('No brands in database.');
        }

        (new SlugRedirectResolver())->record('brand', 'zz-old-brand-slug', $brand->brand_slug);

        $response = $this->get('/lagos/zz-old-brand-slug');

        $response->assertStatus(301)
                 ->assertRedirect('/lagos/' . $brand->brand_slug);
    }

    public function test_flat_location_category_brand_url_redirects_to_new_slug(): void
    {
        $brand = Brands::with('subCategory.category')->whereHas('subCategory.category')->first();

        if (!$brand || !$brand->subCategory || !$brand->subCategory->category) {
            $this->markTestSkipped('No brand with full category/subcategory chain in database.');
        }

        $cat = $brand->subCategory->category;

        (new SlugRedirectResolver())->record('brand', 'zz-old-cat-brand-slug', $brand->brand_slug);

        $response = $this->get("/lagos/{$cat->category_slug}/zz-old-cat-brand-slug");

        $response->assertStatus(301)
                 ->assertRedirect("/lagos/{$cat->category_slug}/{$brand->brand_slug}");
    }

    public function test_unknown_slug_without_redirect_still_404s(): void
    {
        $response = $this->get('/lagos/zz-totally-unknown-slug-xyz');

        $response->assertStatus(404);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/Taxonomy/SlugRedirectRoutesTest.php`
Expected: FAIL — both redirect tests get a 404 instead of a 301 (the third, `test_unknown_slug_without_redirect_still_404s`, already passes since that behavior is unchanged).

- [ ] **Step 3: Inject `SlugRedirectResolver` into `SearchFilter`**

`app/Http/Controllers/Shop/SearchFilter.php:26-29` currently:

```php
    public function __construct(
        private FilterService $filterService,
        private AdvertQueryService $advertQueryService,
    ) {}
```

Change to:

```php
    public function __construct(
        private FilterService $filterService,
        private AdvertQueryService $advertQueryService,
        private SlugRedirectResolver $slugRedirects,
    ) {}
```

Add the import near the other `use App\Services\...` line:

```php
use App\Services\SlugRedirectResolver;
```

- [ ] **Step 4: Add the redirect fallback to `location_router()`**

`app/Http/Controllers/Shop/SearchFilter.php:102-113` currently:

```php
    public function location_router($location, $slug)
    {
        if (Category::where('category_slug', $slug)->exists()) {
            return $this->location_category(request(), $location, $slug);
        } elseif (SubCategory::where('sub_cat_slug', $slug)->exists()) {
            return $this->location_subcat(request(), $location, $slug);
        } elseif (Brands::where('brand_slug', $slug)->exists()) {
            return $this->location_brand(request(), $location, $slug);
        }

        abort(404);
    }
```

Change to:

```php
    public function location_router($location, $slug)
    {
        if (Category::where('category_slug', $slug)->exists()) {
            return $this->location_category(request(), $location, $slug);
        } elseif (SubCategory::where('sub_cat_slug', $slug)->exists()) {
            return $this->location_subcat(request(), $location, $slug);
        } elseif (Brands::where('brand_slug', $slug)->exists()) {
            return $this->location_brand(request(), $location, $slug);
        }

        if ($newSlug = $this->slugRedirects->resolve('subcategory', $slug)) {
            return redirect("/{$location}/{$newSlug}", 301);
        }

        if ($newSlug = $this->slugRedirects->resolve('brand', $slug)) {
            return redirect("/{$location}/{$newSlug}", 301);
        }

        abort(404);
    }
```

- [ ] **Step 5: Add the redirect fallback to `location_category_brand()`**

`app/Http/Controllers/Shop/SearchFilter.php`, the end of `location_category_brand()` (around line 385-391) currently:

```php
        $conditionMap = $this->conditionSlugsForCategory($cat->id);
        if ($conditionMap && isset($conditionMap[$slug])) {
            return $this->renderLocationCategoryCondition($request, $location, $category_slug, $cat, $slug, $conditionMap[$slug]);
        }

        abort(404);
    }
```

Change to:

```php
        $conditionMap = $this->conditionSlugsForCategory($cat->id);
        if ($conditionMap && isset($conditionMap[$slug])) {
            return $this->renderLocationCategoryCondition($request, $location, $category_slug, $cat, $slug, $conditionMap[$slug]);
        }

        if ($newSlug = $this->slugRedirects->resolve('brand', $slug)) {
            $redirectBrand = Brands::where('brand_slug', $newSlug)
                ->whereHas('subCategory', fn ($q) => $q->where('cat_id', $cat->id))
                ->first();

            if ($redirectBrand) {
                return redirect("/{$location}/{$category_slug}/{$newSlug}", 301);
            }
        }

        abort(404);
    }
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test tests/Feature/Taxonomy/SlugRedirectRoutesTest.php`
Expected: PASS (3 tests).

- [ ] **Step 7: Run the full suite to check for regressions**

Run: `php artisan test`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Shop/SearchFilter.php tests/Feature/Taxonomy/SlugRedirectRoutesTest.php
git commit -m "$(cat <<'EOF'
301-redirect renamed flat location/brand URLs instead of 404ing

location_router() and location_category_brand() now check
slug_redirects before giving up, so URLs indexed under the old
numeric-suffixed slugs (other-14, apple-2) keep their SEO equity once
the backfill command renames the underlying rows.
EOF
)"
```

---

### Task 6: Redirect fallback in `AdvertController` (nested `/category/{cat}/{subcat}` and `/category/{cat}/{subcat}/{brand}`)

**Files:**
- Modify: `app/Http/Controllers/User/AdvertController.php`
- Modify: `tests/Feature/Taxonomy/SlugRedirectRoutesTest.php`

**Interfaces:**
- Consumes: `SlugRedirectResolver::resolve()` (Task 3).

- [ ] **Step 1: Add the failing tests to the existing test file**

Add to `tests/Feature/Taxonomy/SlugRedirectRoutesTest.php` (same class from Task 5), before the final closing `}`:

```php
    public function test_nested_category_subcat_brand_url_redirects_to_new_slug(): void
    {
        $brand = \App\Models\Brands::with('subCategory.category')->whereHas('subCategory.category')->first();

        if (!$brand || !$brand->subCategory || !$brand->subCategory->category) {
            $this->markTestSkipped('No brand with full category/subcategory chain in database.');
        }

        $cat = $brand->subCategory->category;
        $subcat = $brand->subCategory;

        (new SlugRedirectResolver())->record('brand', 'zz-old-nested-brand-slug', $brand->brand_slug);

        $response = $this->get("/category/{$cat->category_slug}/{$subcat->sub_cat_slug}/zz-old-nested-brand-slug");

        $response->assertStatus(301)
                 ->assertRedirect("/category/{$cat->category_slug}/{$subcat->sub_cat_slug}/{$brand->brand_slug}");
    }

    public function test_nested_subcategory_url_redirects_to_new_slug(): void
    {
        $subcat = \App\Models\SubCategory::whereHas('category')->first();

        if (!$subcat || !$subcat->category) {
            $this->markTestSkipped('No subcategory with a category in database.');
        }

        (new SlugRedirectResolver())->record('subcategory', 'zz-old-nested-subcat-slug', $subcat->sub_cat_slug);

        $response = $this->get("/category/{$subcat->category->category_slug}/zz-old-nested-subcat-slug");

        $response->assertStatus(301)
                 ->assertRedirect("/category/{$subcat->category->category_slug}/{$subcat->sub_cat_slug}");
    }
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/Taxonomy/SlugRedirectRoutesTest.php`
Expected: FAIL — both new tests get a 404 (`AdvertController::sub_category`/`brand` still use `firstOrFail()`).

- [ ] **Step 3: Inject `SlugRedirectResolver` into `AdvertController`**

`app/Http/Controllers/User/AdvertController.php:42-45` currently:

```php
    public function __construct(
        private AdvertQueryService $advertQueryService,
        private FilterService $filterService,
    ) {}
```

Change to:

```php
    public function __construct(
        private AdvertQueryService $advertQueryService,
        private FilterService $filterService,
        private \App\Services\SlugRedirectResolver $slugRedirects,
    ) {}
```

(Use the fully-qualified class name inline since this file doesn't otherwise group `use App\Services\...` imports near the top — check the existing `use` block and add `use App\Services\SlugRedirectResolver;` there instead if one already exists for `App\Services\*`, to match file convention.)

- [ ] **Step 4: Add the redirect fallback to `sub_category()`**

`app/Http/Controllers/User/AdvertController.php:946-949` currently:

```php
    public function sub_category(Request $request, $category_slug, $subcat_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();
```

Change to:

```php
    public function sub_category(Request $request, $category_slug, $subcat_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->first();

        if (!$subcat) {
            $newSlug = $this->slugRedirects->resolve('subcategory', $subcat_slug);
            $redirectTarget = $newSlug
                ? SubCategory::where('sub_cat_slug', $newSlug)->where('cat_id', $cat->id)->first()
                : null;

            if ($redirectTarget) {
                return redirect("/category/{$category_slug}/{$newSlug}", 301);
            }

            abort(404);
        }
```

- [ ] **Step 5: Add the redirect fallback to `brand()`**

`app/Http/Controllers/User/AdvertController.php:981-985` currently:

```php
    public function brand(Request $request, $category_slug, $subcat_slug, $brand_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();
        $brand = Brands::where('brand_slug', $brand_slug)->where('subcat_id', $subcat->id)->firstOrFail();
```

Change to:

```php
    public function brand(Request $request, $category_slug, $subcat_slug, $brand_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->first();

        if (!$subcat) {
            $newSubcatSlug = $this->slugRedirects->resolve('subcategory', $subcat_slug);
            $redirectSubcat = $newSubcatSlug
                ? SubCategory::where('sub_cat_slug', $newSubcatSlug)->where('cat_id', $cat->id)->first()
                : null;

            if ($redirectSubcat) {
                return redirect("/category/{$category_slug}/{$newSubcatSlug}/{$brand_slug}", 301);
            }

            abort(404);
        }

        $brand = Brands::where('brand_slug', $brand_slug)->where('subcat_id', $subcat->id)->first();

        if (!$brand) {
            $newBrandSlug = $this->slugRedirects->resolve('brand', $brand_slug);
            $redirectBrand = $newBrandSlug
                ? Brands::where('brand_slug', $newBrandSlug)->where('subcat_id', $subcat->id)->first()
                : null;

            if ($redirectBrand) {
                return redirect("/category/{$category_slug}/{$subcat_slug}/{$newBrandSlug}", 301);
            }

            abort(404);
        }
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test tests/Feature/Taxonomy/SlugRedirectRoutesTest.php`
Expected: PASS (5 tests total).

- [ ] **Step 7: Run the full suite to check for regressions**

Run: `php artisan test`
Expected: PASS — in particular confirm `test_subcategory_page_returns_200` and `test_brand_page_returns_200` in `SearchFilterRoutesTest.php` still pass (the `firstOrFail()` → `first()` + fallback change must not alter the success path).

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/User/AdvertController.php tests/Feature/Taxonomy/SlugRedirectRoutesTest.php
git commit -m "$(cat <<'EOF'
301-redirect renamed nested category/subcat/brand URLs instead of 404ing

sub_category() and brand() now fall back to slug_redirects before
aborting, matching the flat-route behavior added for SearchFilter.
EOF
)"
```

---

### Task 7: Run the real backfill, regenerate the sitemap, verify

**Files:** none (operational verification only).

**Interfaces:** none — this task exercises everything built in Tasks 1-6 against real data.

- [ ] **Step 1: Run the backfill for real**

Run: `php artisan taxonomy:disambiguate-slugs`

Expected: same shape of output as the Task 4 dry-run, but this time rows are actually saved and `slug_redirects` is populated. Confirm the final per-type "changed" counts roughly match the dry-run counts from Task 4.

- [ ] **Step 2: Spot-check a handful of old URLs redirect correctly**

Pick 3-5 `old_slug -> new_slug` pairs from the command's output (mix of `subcategory` and `brand` types) and verify each old URL 301s:

```bash
curl -sI "https://www.marketplace.ng/category/electronics/tv-dvd-equipment/other-63" | head -5
```

Expected: `HTTP/1.1 301` with a `Location:` header pointing at the new slug.

(Substitute one flat `/{location}/{old-slug}` URL and one `/category/{cat}/{old-subcat-slug}` URL from the actual output too, to cover all four redirect call sites.)

- [ ] **Step 3: Regenerate the sitemap and confirm numbered slugs are gone**

Run: `php artisan sitemap:generate`

Then re-run the same counts used to verify Phase B earlier:

```bash
cd public
echo "locations file - other-N count:"; grep -oE '<loc>[^<]+</loc>' sitemap-locations.xml | grep -cE '/other-[0-9]+<'
echo "locations file - no-name count:"; grep -oE '<loc>[^<]+</loc>' sitemap-locations.xml | grep -c 'no-name'
echo "locations file - any -N suffix count:"; grep -oE '<loc>[^<]+</loc>' sitemap-locations.xml | grep -cE '\-[0-9]+<'
```

Expected: all three counts drop dramatically (down from the post-Phase-B baseline of 132 / 4 / 266) but not necessarily to exactly 0. The collision regex (`-[0-9]+$`) also matches real product names that legitimately end in a trailing number — `f-150`, `rx-350`, `gla-250`, `sony-playstation-4`, and similar. These are not collisions: when the backfill command recomputes their slug, the recomputed value is identical to the existing one, so the row is correctly left untouched (the command's per-row transaction is rolled back for these no-op cases; see `DisambiguateTaxonomySlugs::reslug()`). A future operator seeing a small non-zero remaining count after a successful run should not read that as a failed or incomplete backfill — it's expected residue from genuine product-name collisions with the regex, not missed renames.

- [ ] **Step 4: Commit the regenerated sitemap files**

```bash
cd /home/www/laravel/marketplace
git add public/sitemap-adverts.xml public/sitemap-blog.xml public/sitemap-brands.xml public/sitemap-categories.xml public/sitemap-locations.xml public/sitemap-static.xml public/sitemap.xml
git commit -m "$(cat <<'EOF'
Regenerate sitemap after taxonomy slug disambiguation backfill

No more other-N, no-name, or -N numeric-suffixed brand/subcategory
slugs in the location-facet or category sitemap sections.
EOF
)"
```

---

## Self-Review

**Spec coverage:**
- Contextual suffix instead of numeric counter → Task 1 (algorithm) + Task 2 (wiring into all 3 affected models).
- Fixes existing ~1,303 Brands rows + 3 SubCategory rows (+ 767 VehicleModel rows found during investigation, included for consistency since it's the identical defect) → Task 4.
- Prevents recurrence for all future inserts → Task 2 (wiring is permanent, not a one-time script).
- 301 redirects preserving SEO equity for already-indexed ugly-slug URLs → Task 3 (storage) + Tasks 5-6 (all 4 route entry points: flat `/{location}/{slug}`, flat `/{location}/{category}/{slug}`, nested `/category/{cat}/{subcat}`, nested `/category/{cat}/{subcat}/{brand}`).
- Verification against real data and the sitemap → Task 7.
- No schema/relationship rewrite, no admin-panel changes needed (verified during investigation that `ManageCategories::brand()` doesn't set `brand_slug` explicitly, so it picks up the new logic automatically with zero changes) — correctly out of scope.

**Placeholder scan:** No TBD/TODO/"add error handling"-style steps; every step has real, complete code.

**Type consistency:** `TaxonomySlugDisambiguator::suffix()` signature is identical everywhere it's referenced (Task 1 definition, Task 2 three call sites). `SlugRedirectResolver::resolve()`/`record()` signatures are identical across Task 3 (definition), Task 4 (command), Task 5/6 (controllers). The `sluggable_type` string values (`'brand'`, `'subcategory'`, `'model'`) are used consistently across Tasks 3, 4, 5, and 6.

---

## Production Deployment Runbook

This branch changes both code (route/controller behavior) and data (a backfill that renames live rows). The order below is not interchangeable — deploying out of order opens a window where already-indexed URLs hard-404 instead of redirecting.

1. **Run `php artisan migrate` on production first, before (or atomically with) deploying the new code.** This creates the `slug_redirects` table. It's a purely additive migration (new table only, no changes to existing tables) — no risk to existing data or schema.

   The migration must land no later than the code: every redirect-fallback call site (`SlugRedirectResolver::resolve()`, called from `SearchFilter.php` and `AdvertController.php`) unconditionally runs a `select ... from slug_redirects` whenever a live slug lookup misses — which is exactly the 404-bound traffic this branch exists to help. If the new code is live while `slug_redirects` doesn't exist yet, every one of those misses throws a `QueryException` (table doesn't exist) instead of returning a clean 404, i.e. a 500 in place of a 404. Run the migration first (or deploy code and migration together in the same step/script) so there is never a window where the new code is live against a database that doesn't yet have the table.

2. **Deploy the code changes to production**, via this project's existing SCP/SSH deploy workflow (not FTP/`deploy.sh` — see this project's "Prefer SCP Deploy" convention). With the `slug_redirects` table already in place from step 1, this is a no-op for existing rows and for the redirect-fallback queries — the table is simply empty and no slugs have changed yet, so behavior for real traffic is unaffected until the backfill runs. The redirect fallback routes (Tasks 5/6) are now live so that once the backfill runs, old URLs 301 instead of 404ing.

3. **Run `php artisan taxonomy:disambiguate-slugs --dry-run` on production and review the output.** Read through the `subcategory` / `brand` / `model` sections and spot-check that `old -> new` pairs look sane (parent-qualified, not still numeric).

   Note: the dry-run holds a single outer transaction with row locks on `sub_categories`, `brands`, and `models` for its full duration — roughly 6-7 seconds against the current data volume (~1,880 rows). Schedule this (and step 4) for an off-peak window; a multi-second lock on these tables is enough to cause a visible stall on live category/brand/model page loads if run during peak traffic.

4. **Run `php artisan taxonomy:disambiguate-slugs` for real on production.** The command will prompt for confirmation before making any changes (`This will rename N rows and cannot be easily undone. Continue?`) — confirm only after the dry-run output in step 3 has been reviewed and looks correct. This is the step that actually renames rows and populates `slug_redirects`. If running this non-interactively (e.g. piped through SSH in a script rather than an interactive shell), pass `--force` to skip the confirmation prompt — otherwise the command silently no-ops with exit code 0 under a non-TTY invocation, which looks like success but changes nothing.

5. **Regenerate the sitemap ON THE PRODUCTION HOST** — run `php artisan sitemap:generate` there, not locally.

   **Critical warning:** the `public/sitemap-*.xml` files committed in this branch are LOCAL DEV artifacts — every `<loc>` in them points at `http://127.0.0.1:8030/...`. They exist in this branch purely to prove the local backfill worked end-to-end (Task 7, local verification). They must **never** be copied or SCP'd to `public_html/` on production. Production needs its own sitemap regeneration, run on the production host, against its own domain — do not reuse or deploy the dev-generated files.

6. **Spot-check a handful of redirects on production**, same approach as the local Task 7 verification: pick 3-5 `old_slug -> new_slug` pairs from step 4's output (mix of `subcategory`/`brand`/`model` types, across all four redirect call sites) and confirm each old URL returns a 301 with a `Location:` header pointing at the new slug, e.g.:

   ```bash
   curl -sI "https://www.marketplace.ng/category/electronics/tv-dvd-equipment/other-63" | head -5
   ```
