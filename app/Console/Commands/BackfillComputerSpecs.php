<?php

namespace App\Console\Commands;

use App\Models\Advert;
use App\Models\ComputerDetail;
use App\Support\ComputerSpecs;
use App\Support\DeviceModels;
use Illuminate\Console\Command;

/**
 * Fills Laptops & Computers specs (processor, RAM, storage, storage type)
 * from each listing's title and description. Only empty specs are filled,
 * so a seller's own choices are never overwritten; safe to re-run.
 */
class BackfillComputerSpecs extends Command
{
    protected $signature = 'computers:backfill-specs {--dry-run} {--show-titles : List what each listing gets}';

    protected $description = 'Fill empty laptop specs (processor, RAM, storage) from the advert title and description';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $filled = array_fill_keys(ComputerSpecs::FIELDS, 0);
        $values = array_fill_keys(ComputerSpecs::FIELDS, []);
        $listings = 0;
        $changed = 0;

        $adverts = Advert::where('sub_category', DeviceModels::COMPUTERS_SUBCATEGORY_ID)->with('computer')->orderBy('id');

        foreach ($adverts->lazy() as $advert) {
            $listings++;
            $current = $advert->computer ? $advert->computer->only(ComputerSpecs::FIELDS) : array_fill_keys(ComputerSpecs::FIELDS, null);
            $parsed = ComputerSpecs::parse($advert->ad_title . "\n" . $advert->description);

            $updates = [];
            foreach (ComputerSpecs::FIELDS as $field) {
                if (empty($current[$field]) && isset($parsed[$field])) {
                    $updates[$field] = $parsed[$field];
                    $filled[$field]++;
                    $values[$field][$parsed[$field]] = ($values[$field][$parsed[$field]] ?? 0) + 1;
                }
            }

            if (!$updates) {
                continue;
            }
            $changed++;

            if ($this->option('show-titles')) {
                $this->line(sprintf('  %-6d %-45s %s', $advert->id, mb_strimwidth((string) $advert->ad_title, 0, 44, '…'), implode(' · ', $updates)));
            }

            if (!$dryRun) {
                // Query builder for existing rows: no touched timestamps on the advert
                $advert->computer
                    ? ComputerDetail::where('id', $advert->computer->id)->update($updates)
                    : ComputerDetail::create(['advert_id' => $advert->id, 'brand_id' => $advert->brand] + $updates);
            }
        }

        $this->info(sprintf('%d of %d laptop listings %s specs.', $changed, $listings, $dryRun ? 'would get' : 'got'));
        $this->table(['Spec', 'Filled', 'Values'], array_map(fn ($field) => [
            ComputerSpecs::LABELS[$field],
            $filled[$field],
            collect($values[$field])->sortDesc()->map(fn ($n, $v) => "{$v} {$n}")->take(8)->implode(', '),
        ], ComputerSpecs::FIELDS));

        if ($dryRun) {
            $this->warn('Dry run — nothing changed.');
        }

        return self::SUCCESS;
    }
}
