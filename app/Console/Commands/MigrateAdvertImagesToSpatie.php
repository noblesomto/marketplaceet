<?php
namespace App\Console\Commands;

use App\Models\Advert;
use App\Models\AdvertImage;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MigrateAdvertImagesToSpatie extends Command
{
    protected $signature = 'migrate:advert-images-to-spatie {--chunk=100}';
    protected $description = 'Migrate existing advert images to Spatie Media Library';

    public function handle()
    {
        $this->info('Starting migration of advert images to Spatie Media Library...');

        $chunkSize = (int) $this->option('chunk');
        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        AdvertImage::with('advert')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($advertImages) use (&$migrated, &$skipped, &$failed) {
                foreach ($advertImages as $advertImage) {
                    try {
                        $advert = $advertImage->advert;

                        // ✅ Check if already migrated
                        $already = Media::where('model_type', Advert::class)
                            ->where('model_id', $advert?->id)
                            ->where('custom_properties->migrated_from_id', $advertImage->id)
                            ->exists();

                        if ($already) {
                            $skipped++;
                            $this->line("⏭ Skipped image ID {$advertImage->id} (already migrated)");
                            continue;
                        }

                        $imagePath = public_path('uploads/images/' . $advertImage->image);

                        if (file_exists($imagePath) && $advert) {
                            $media = $advert
                                ->addMedia($imagePath)
                                ->withCustomProperties([
                                    'position' => $advertImage->position,
                                    'migrated_from_id' => $advertImage->id
                                ])
                                ->usingName($advertImage->image)
                                ->usingFileName($advertImage->image)
                                ->toMediaCollection('images');

                            $media->order_column = $advertImage->position;
                            $media->save();

                            $migrated++;
                            $this->line("✓ Migrated image ID {$advertImage->id}: {$advertImage->image}");
                        } else {
                            $failed++;
                            $this->error("✗ Failed to migrate image ID {$advertImage->id}: File not found or advert missing");
                        }
                    } catch (\Exception $e) {
                        $failed++;
                        $this->error("✗ Failed to migrate image ID {$advertImage->id}: " . $e->getMessage());
                    }
                }
            });

        $this->info("\nMigration completed!");
        $this->info("Successfully migrated: {$migrated}");
        $this->line("Skipped (already migrated): {$skipped}");
        $this->error("Failed: {$failed}");

        if ($failed === 0) {
            $this->warn("\n⚠️  Migration finished without errors.");
            if ($skipped > 0) {
                $this->warn("Some images were skipped because they were already migrated.");
            } else {
                $this->warn("You can now safely remove the old AdvertImage model and related code.");
                $this->warn("Don't forget to drop the advert_images table when you're ready.");
            }
        }
    }
}
