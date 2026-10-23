<?php
// app/Console/Commands/CleanupOriginalImages.php

namespace App\Console\Commands;

use App\Models\Advert; // Replace with your model
use App\Services\MediaImageService;
use Illuminate\Console\Command;

class CleanupOriginalImages extends Command
{
    protected $signature = 'media:cleanup-originals
                            {--model=Advert : The model to clean up}
                            {--collection=images : The media collection}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Delete original image files to save space, keeping only conversions';

    public function handle()
    {
        $modelClass = "App\\Models\\" . $this->option('model');
        $collection = $this->option('collection');
        $dryRun = $this->option('dry-run');

        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist!");
            return 1;
        }

        $imageService = app(MediaImageService::class);
        $models = $modelClass::has('media')->get();

        $totalDeleted = 0;
        $totalSize = 0;

        $this->info("Processing {$models->count()} models...");

        foreach ($models as $model) {
            $media = $model->getMedia($collection);

            foreach ($media as $mediaItem) {
                $originalPath = $mediaItem->getPath();

                if (file_exists($originalPath)) {
                    $fileSize = filesize($originalPath);
                    $fileSizeMB = round($fileSize / 1024 / 1024, 2);

                    // Check if conversions exist
                    $largeExists = file_exists($mediaItem->getPath('large'));
                    $optimizedExists = file_exists($mediaItem->getPath('optimized'));

                    if ($largeExists && $optimizedExists) {
                        if ($dryRun) {
                            $this->line("Would delete: {$originalPath} ({$fileSizeMB}MB)");
                        } else {
                            if (unlink($originalPath)) {
                                $this->line("Deleted: {$originalPath} ({$fileSizeMB}MB)");
                                $totalDeleted++;
                                $totalSize += $fileSize;
                            } else {
                                $this->error("Failed to delete: {$originalPath}");
                            }
                        }
                    } else {
                        $this->warn("Skipping {$originalPath} - conversions missing");
                    }
                }
            }
        }

        $totalSizeMB = round($totalSize / 1024 / 1024, 2);

        if ($dryRun) {
            $this->info("Dry run complete. Would delete {$totalDeleted} files, saving {$totalSizeMB}MB");
        } else {
            $this->info("Cleanup complete! Deleted {$totalDeleted} files, saved {$totalSizeMB}MB");
        }

        return 0;
    }
}

// Don't forget to register this command in app/Console/Kernel.php:
// protected $commands = [
//     Commands\CleanupOriginalImages::class,
// ];
