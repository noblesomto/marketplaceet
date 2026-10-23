<?php

namespace App\Console\Commands;

use App\Models\Brands;
use App\Services\SlugRedirectResolver;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeBrands extends Command
{
    protected $signature = 'taxonomy:merge-brands
        {keep : ID or slug of the brand row to keep}
        {merge* : IDs or slugs of the brand rows to fold into it (deleted afterward)}
        {--name= : Rename the kept brand and regenerate its slug from this name}
        {--dry-run : Preview changes without writing them}
        {--force : Skip the confirmation prompt (required for non-interactive/cron/CI invocations)}';

    protected $description = 'Merge duplicate Brand rows into one: reassigns their adverts/models to the kept brand, records old-slug redirects, and deletes the merged rows.';

    public function handle(SlugRedirectResolver $redirects): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $keep = $this->resolveBrand($this->argument('keep'));
        if (!$keep) {
            $this->error("Brand not found: {$this->argument('keep')}");

            return self::FAILURE;
        }

        $mergeBrands = collect($this->argument('merge'))
            ->map(fn ($ref) => $this->resolveBrand($ref));

        if ($mergeBrands->contains(null)) {
            $this->error('One or more "merge" brands could not be found.');

            return self::FAILURE;
        }

        $mergeBrands = $mergeBrands->reject(fn ($b) => $b->id === $keep->id)->unique('id')->values();

        if ($mergeBrands->isEmpty()) {
            $this->info('Nothing to merge — no distinct brand rows given.');

            return self::SUCCESS;
        }

        $mergeIds = $mergeBrands->pluck('id')->all();
        $adCount = DB::table('adverts')->whereIn('brand', $mergeIds)->count();
        $modelCount = DB::table('models')->whereIn('brand_id', $mergeIds)->count();
        $newName = $this->option('name');

        $this->info("Keeping: #{$keep->id} \"{$keep->brand}\" ({$keep->brand_slug})");
        foreach ($mergeBrands as $b) {
            $this->info("  merging in: #{$b->id} \"{$b->brand}\" ({$b->brand_slug})");
        }
        $this->info("Will reassign {$adCount} advert(s) and {$modelCount} model(s), then delete " . $mergeBrands->count() . ' row(s).');
        if ($newName) {
            $this->info("Kept brand will be renamed to \"{$newName}\" and re-slugged.");
        }

        if ($dryRun) {
            $this->info('Dry run — no changes made.');

            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Apply this merge? It cannot be easily undone. Continue?')) {
            $this->info('Aborted. No changes were made.');

            return self::SUCCESS;
        }

        DB::beginTransaction();

        try {
            $oldKeepSlug = $keep->brand_slug;

            if ($newName) {
                $keep->brand = $newName;
                (new SlugService())->slug($keep, true);
                $keep->save();
            }

            DB::table('adverts')->whereIn('brand', $mergeIds)->update(['brand' => $keep->id]);
            DB::table('models')->whereIn('brand_id', $mergeIds)->update(['brand_id' => $keep->id]);

            foreach ($mergeBrands as $b) {
                $redirects->record('brand', $b->brand_slug, $keep->brand_slug);
            }

            if ($newName && $oldKeepSlug !== $keep->brand_slug) {
                $redirects->record('brand', $oldKeepSlug, $keep->brand_slug);
            }

            Brands::whereIn('id', $mergeIds)->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->info("Done. {$keep->brand_slug} now covers {$adCount} previously-scattered advert(s).");

        return self::SUCCESS;
    }

    private function resolveBrand(string $ref): ?Brands
    {
        if (ctype_digit($ref)) {
            return Brands::find((int) $ref);
        }

        return Brands::where('brand_slug', $ref)->first();
    }
}
