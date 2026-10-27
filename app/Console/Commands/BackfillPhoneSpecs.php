<?php

namespace App\Console\Commands;

use App\Models\Advert;
use App\Models\PhoneDetail;
use App\Support\PhoneSpecs;
use Illuminate\Console\Command;

/**
 * Fills Mobile Phones specs (RAM, storage) from each listing's title and
 * description. Only empty specs are filled, so a seller's own choices are
 * never overwritten; safe to re-run.
 */
class BackfillPhoneSpecs extends Command
{
    protected $signature = 'phones:backfill-specs {--dry-run} {--show-titles : List what each listing gets}';

    protected $description = 'Fill empty phone specs (RAM, storage) from the advert title and description';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $filled = array_fill_keys(PhoneSpecs::FIELDS, 0);
        $values = array_fill_keys(PhoneSpecs::FIELDS, []);
        $listings = 0;
        $changed = 0;

        // Phone listings without a phone_details row have nothing to attach specs to
        $adverts = Advert::where('sub_category', 6)->has('phone')->with('phone')->orderBy('id');

        foreach ($adverts->lazy() as $advert) {
            $listings++;
            $parsed = PhoneSpecs::parse((string) $advert->ad_title, (string) $advert->description);

            $updates = [];
            foreach (PhoneSpecs::FIELDS as $field) {
                if (empty($advert->phone->{$field}) && isset($parsed[$field])) {
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
                // Query builder: no touched timestamps on the advert
                PhoneDetail::where('id', $advert->phone->id)->update($updates);
            }
        }

        $this->info(sprintf('%d of %d phone listings %s specs.', $changed, $listings, $dryRun ? 'would get' : 'got'));
        $this->table(['Spec', 'Filled', 'Values'], array_map(fn ($field) => [
            PhoneSpecs::LABELS[$field],
            $filled[$field],
            collect($values[$field])->sortDesc()->map(fn ($n, $v) => "{$v} {$n}")->take(8)->implode(', '),
        ], PhoneSpecs::FIELDS));

        if ($dryRun) {
            $this->warn('Dry run — nothing changed.');
        }

        return self::SUCCESS;
    }
}
