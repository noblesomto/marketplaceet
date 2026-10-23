<?php

namespace App\Console\Commands;

use App\Models\Advert;
use App\Models\AdSetting;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Remediates adverts left with too few (or zero) usable images by the
 * image-deletion bugs fixed alongside this command: stale image counts,
 * a validation bypass, and DB transactions that couldn't undo already-deleted
 * files. Those bugs let live adverts end up with fewer images than the
 * configured minimum, or with `media` rows whose underlying files are gone
 * (broken/placeholder image icons on the storefront).
 *
 * This command finds those adverts and pauses them (ad_status -> 'disabled')
 * instead of leaving them live and broken, and notifies the seller so they
 * can add images back and ask an admin to reactivate.
 */
class FixAdvertsWithBrokenImages extends Command
{
    protected $signature = 'adverts:fix-broken-images
        {--dry-run : Report affected adverts without changing anything}
        {--advert-id= : Only check this specific advert ID (for targeted debugging)}
        {--missing-files-only : Within the below-minimum set, only flag adverts where the shortfall is evidenced by a media DB row whose file is actually missing on disk — the exact bug signature — skipping adverts that are below the minimum but have no missing files (e.g. legacy listings posted before the minimum was enforced)}';

    protected $description = 'Find live adverts with fewer usable images than the minimum (missing files or too few) and pause them';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $advertId = $this->option('advert-id');
        $missingFilesOnly = (bool) $this->option('missing-files-only');
        $minImages = (int) AdSetting::getValue('min_images', 3);
        $jobsCvCategories = [3, 18]; // Jobs and CVs have no image minimum

        $this->info(($dryRun ? '[DRY RUN] ' : '') . ($missingFilesOnly
            ? "Scanning active adverts for fewer than {$minImages} usable image(s) caused by a missing file..."
            : "Scanning active adverts for fewer than {$minImages} usable image(s)..."));

        $affected = 0;
        $checked = 0;

        Advert::with('media')
            ->where('ad_status', 'active')
            ->whereNotIn('category', $jobsCvCategories)
            ->when($advertId, fn ($query) => $query->where('id', $advertId))
            ->chunkById(200, function ($adverts) use (&$affected, &$checked, $minImages, $dryRun, $missingFilesOnly) {
                foreach ($adverts as $advert) {
                    $checked++;

                    // Use the 'thumbnail' conversion, not the original — originals
                    // are intentionally deleted after upload (see
                    // MediaImageService::deleteOriginalAfterConversions) to save
                    // space, so checking the original would always report 0.
                    $totalRows = $advert->getMedia('images')->count();
                    $usableCount = $advert->getMedia('images')
                        ->filter(fn ($media) => file_exists($media->getPath('thumbnail')))
                        ->count();

                    $belowMinimum = $usableCount < $minImages;
                    $hasMissingFile = $usableCount < $totalRows;

                    $isAffected = $missingFilesOnly
                        ? ($belowMinimum && $hasMissingFile)
                        : $belowMinimum;

                    if (!$isAffected) {
                        continue;
                    }

                    $affected++;

                    $this->line(sprintf(
                        '  Advert #%d "%s" (user %d): %d usable image(s) of %d record(s), needs %d',
                        $advert->id,
                        $advert->ad_title,
                        $advert->user_id,
                        $usableCount,
                        $totalRows,
                        $minImages
                    ));

                    if ($dryRun) {
                        continue;
                    }

                    $advert->update(['ad_status' => 'disabled']);

                    try {
                        // Advert::user_id references users.user_id, NOT users.id —
                        // notifications.user_id has an FK to users.id, so it must be
                        // resolved through the relationship, not read off the Advert row.
                        $ownerId = $advert->user->id ?? null;

                        if ($ownerId) {
                            Notification::create([
                                'user_id'   => $ownerId,
                                'seller_id' => $ownerId,
                                'advert_id' => $advert->id,
                                'type'      => 'Advert Paused',
                                'message'   => "Your advert \"{$advert->ad_title}\" was paused because some of its images are missing. Please re-upload your images and contact support to reactivate it.",
                                'is_read'   => 0,
                            ]);
                        } else {
                            Log::warning("Could not resolve owner for advert {$advert->id} (user_id {$advert->user_id}); no notification sent");
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to notify user {$advert->user_id} about paused advert {$advert->id}: " . $e->getMessage());
                    }

                    Log::warning("Paused advert {$advert->id} (ad_status: active -> disabled): {$usableCount} usable of {$totalRows} image record(s)");
                }
            });

        $this->newLine();
        $this->info("Checked {$checked} active advert(s).");

        if ($affected === 0) {
            $this->info('No adverts found below the minimum image count.');
            return 0;
        }

        $this->warn(
            $dryRun
                ? "{$affected} advert(s) would be paused. Re-run without --dry-run to apply."
                : "{$affected} advert(s) paused (ad_status set to 'disabled') and their sellers notified."
        );

        return 0;
    }
}
