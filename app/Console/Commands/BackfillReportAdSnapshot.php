<?php

namespace App\Console\Commands;

use App\Models\Reports;
use Illuminate\Console\Command;

/**
 * Populates reported_ad_title/reported_ad_number and the reported_seller_*
 * identity fields on existing reports whose advert still exists, so that
 * ad and seller info survive if that advert is later deleted (see
 * App\Models\Reports display accessors). This is what lets an admin still
 * identify who posted a deleted ad — e.g. to hand over to law enforcement
 * on request. Reports whose advert is already gone can't be recovered —
 * that data was never stored anywhere — and will keep showing "Ad deleted".
 */
class BackfillReportAdSnapshot extends Command
{
    protected $signature = 'reports:backfill-ad-snapshot {--dry-run}';

    protected $description = 'Backfill ad title/number and seller identity snapshot onto existing reports whose advert still exists';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;
        $alreadyOrphaned = 0;

        Reports::with('adverts.owner')
            ->where(function ($query) {
                $query->whereNull('reported_ad_title')
                    ->orWhereNull('reported_seller_id');
            })
            ->chunkById(200, function ($reports) use ($dryRun, &$updated, &$alreadyOrphaned) {
                foreach ($reports as $report) {
                    if (! $report->adverts) {
                        $alreadyOrphaned++;
                        continue;
                    }

                    $owner = $report->adverts->owner;
                    $updated++;
                    $this->line(($dryRun ? '[DRY] ' : '') . "#{$report->id}: {$report->adverts->ad_title} (ad_id {$report->adverts->ad_id}) — seller: " . ($owner->name ?? 'unknown'));

                    if ($dryRun) {
                        continue;
                    }

                    $report->timestamps = false;
                    $report->forceFill([
                        'reported_ad_title' => $report->adverts->ad_title,
                        'reported_ad_number' => $report->adverts->ad_id,
                        'reported_seller_id' => $owner->user_id ?? null,
                        'reported_seller_name' => $owner->name ?? null,
                        'reported_seller_phone' => $owner->phone ?? null,
                        'reported_seller_email' => $owner->email ?? null,
                    ])->save();
                }
            });

        $this->info(($dryRun ? 'Would update' : 'Updated') . " {$updated} reports; {$alreadyOrphaned} already orphaned and unrecoverable.");

        return self::SUCCESS;
    }
}
