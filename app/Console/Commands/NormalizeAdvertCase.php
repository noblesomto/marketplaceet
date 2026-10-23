<?php

namespace App\Console\Commands;

use App\Helpers\ContentHelper;
use App\Models\Advert;
use Illuminate\Console\Command;

/**
 * Backfills the ALL-CAPS -> sentence-case normalization (see
 * ContentHelper::normalizeShoutingCase()) onto ad_title/description of
 * adverts that were saved before that normalization existed. Only touches
 * rows where the text is actually detected as shouting; leaves everything
 * else untouched. Does not bump updated_at, since this is a formatting fix,
 * not a real edit by the seller.
 */
class NormalizeAdvertCase extends Command
{
    protected $signature = 'adverts:normalize-case
        {--dry-run : Report affected adverts without changing anything}
        {--chunk=200 : Rows to process per DB chunk}';

    protected $description = 'Convert existing ALL-CAPS ad titles/descriptions to sentence case';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $scanned = 0;
        $updated = 0;

        Advert::query()
            ->orderBy('id')
            ->chunkById((int) $this->option('chunk'), function ($adverts) use ($dryRun, &$scanned, &$updated) {
                foreach ($adverts as $advert) {
                    $scanned++;

                    $newTitle = ContentHelper::normalizeCaseOnly($advert->ad_title);
                    $newDescription = ContentHelper::normalizeCaseOnly($advert->description);

                    $titleChanged = $newTitle !== ($advert->ad_title ?? '');
                    $descriptionChanged = $newDescription !== ($advert->description ?? '');

                    if (! $titleChanged && ! $descriptionChanged) {
                        continue;
                    }

                    $updated++;

                    $what = trim(($titleChanged ? 'title' : '') . ($titleChanged && $descriptionChanged ? '+' : '') . ($descriptionChanged ? 'description' : ''));
                    $this->line(($dryRun ? '[DRY] ' : '') . "#{$advert->id} (ad_id {$advert->ad_id}): {$what}");

                    if ($dryRun) {
                        continue;
                    }

                    $advert->timestamps = false;
                    $advert->forceFill([
                        'ad_title' => $newTitle,
                        'description' => $newDescription,
                    ])->save();
                }
            });

        $this->info(($dryRun ? 'Would update' : 'Updated') . " {$updated} of {$scanned} scanned adverts.");

        return self::SUCCESS;
    }
}
