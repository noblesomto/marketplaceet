<?php

namespace App\Console\Commands;

use App\Models\Brands;
use App\Models\SubCategory;
use App\Models\VehicleModel;
use App\Services\SlugRedirectResolver;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DisambiguateTaxonomySlugs extends Command
{
    protected $signature = 'taxonomy:disambiguate-slugs {--dry-run : Preview changes without writing them} {--force : Skip the confirmation prompt and run immediately (required for non-interactive/cron/CI invocations)}';

    protected $description = 'Re-slug SubCategory/Brands/VehicleModel rows whose slug carries a numeric collision suffix (e.g. other-14), using parent-context suffixes instead, and record old->new redirects.';

    public function handle(SlugRedirectResolver $redirects): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (!$dryRun) {
            $total = SubCategory::whereRaw("sub_cat_slug REGEXP '-[0-9]+$'")->count()
                + Brands::whereRaw("brand_slug REGEXP '-[0-9]+$'")->count()
                + VehicleModel::whereRaw("model_slug REGEXP '-[0-9]+$'")->count();

            if (!$this->option('force') && !$this->confirm("This will rename {$total} rows and cannot be easily undone. Continue?")) {
                $this->info('Aborted. No changes were made.');

                return self::SUCCESS;
            }
        }

        $service = new SlugService();

        // --dry-run wraps the entire run in one outer transaction that is
        // rolled back at the very end (never committed). Rows are still
        // saved in-memory-and-in-DB as they're computed (see reslug()) so
        // that later rows depending on earlier ones (e.g. a Brand reading
        // its SubCategory's slug) see an accurate, cascaded preview within
        // this same transaction — while guaranteeing zero persisted change.
        if ($dryRun) {
            DB::beginTransaction();
        }

        try {
            // Order matters: SubCategory's new slug feeds Brands' suffix source,
            // and Brands' new slug feeds VehicleModel's — process parents first
            // so children pick up the already-renamed parent slug.
            $this->reslug(SubCategory::class, 'sub_cat_slug', 'subcategory', $service, $redirects, $dryRun);
            $this->reslug(Brands::class, 'brand_slug', 'brand', $service, $redirects, $dryRun);
            $this->reslug(VehicleModel::class, 'model_slug', 'model', $service, $redirects, $dryRun);
        } finally {
            if ($dryRun) {
                DB::rollBack();
            }
        }

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

            // EloquentSluggable's makeSlugUnique() short-circuits and returns the
            // slug unchanged whenever it already starts with the freshly recomputed
            // base slug (see SlugService::makeSlugUnique()'s "looks like a suffixed
            // version of our slug" branch) — which is true for every row we're
            // trying to fix here, so it never reaches our uniqueSuffix closure.
            // Blank the column first so this row drops out of the "similar slugs"
            // list Sluggable compares against, forcing it through the real
            // uniqueSuffix closure instead of the short-circuit.
            //
            // Real runs: the blank + recompute + save for this row is wrapped in
            // its own short-lived transaction, committed immediately, so no other
            // request can ever observe the blanked slug value.
            //
            // Dry runs: handle() already opened one outer transaction for the
            // whole command that gets rolled back at the very end, so there's no
            // separate per-row transaction here — the blank + recompute + save
            // below happen inside that outer transaction and are never persisted.
            if (!$dryRun) {
                DB::beginTransaction();
            }

            try {
                $modelClass::where($row->getKeyName(), $row->getKey())->update([$slugColumn => '']);
                $row->refresh();

                $service->slug($row, true);
                $new = $row->{$slugColumn};

                if ($new === $old) {
                    if ($dryRun) {
                        // Restore the blanked value within the still-open outer
                        // transaction (see handle()) — otherwise this row's
                        // slug stays '' for the rest of the run and collides
                        // with the next row we blank under the unique index.
                        $row->save();
                    } else {
                        DB::rollBack();
                    }
                    continue;
                }

                $changed++;
                $this->line("  {$old} -> {$new}");

                // Saved in both modes: for a real run this is the actual
                // persisted rename; for a dry run it's still written (within
                // the outer, never-committed transaction from handle()) so
                // that later rows in this same run — e.g. a Brand reading its
                // SubCategory's slug — see the cascaded, accurate preview.
                $row->save();

                if (!$dryRun) {
                    $redirects->record($type, $old, $new);
                    DB::commit();
                }
            } catch (\Throwable $e) {
                if (!$dryRun) {
                    DB::rollBack();
                }
                throw $e;
            }
        }

        $this->info("{$type}: {$changed} slugs " . ($dryRun ? 'would change' : 'changed'));
    }
}
