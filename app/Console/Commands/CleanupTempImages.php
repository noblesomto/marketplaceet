<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupTempImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp:cleanup-images {--hours=24 : Delete temp images older than this many hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up abandoned temporary images from failed ad submissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $this->info("🗑️  Cleaning up temp images older than {$hours} hours...");

        $tempPath = 'temp/post-ad-images';

        // Check if temp directory exists
        if (!Storage::disk('public')->exists($tempPath)) {
            $this->info('✅ No temp directory found. Nothing to clean.');
            return 0;
        }

        $deletedCount = 0;
        $deletedSize = 0;
        $cutoffTime = Carbon::now()->subHours($hours)->timestamp;

        // Get all files in temp directory
        $files = Storage::disk('public')->allFiles($tempPath);

        foreach ($files as $file) {
            $lastModified = Storage::disk('public')->lastModified($file);

            // Delete if older than cutoff time
            if ($lastModified < $cutoffTime) {
                $fileSize = Storage::disk('public')->size($file);

                if (Storage::disk('public')->delete($file)) {
                    $deletedCount++;
                    $deletedSize += $fileSize;
                    $this->line("  🗑️  Deleted: {$file}");
                }
            }
        }

        // Delete empty directories
        $directories = Storage::disk('public')->directories($tempPath);
        foreach ($directories as $dir) {
            $dirFiles = Storage::disk('public')->allFiles($dir);
            if (empty($dirFiles)) {
                Storage::disk('public')->deleteDirectory($dir);
                $this->line("  📁 Removed empty directory: {$dir}");
            }
        }

        // Format size
        $sizeFormatted = $this->formatBytes($deletedSize);

        $this->newLine();
        $this->info("✅ Cleanup complete!");
        $this->info("   Files deleted: {$deletedCount}");
        $this->info("   Space freed: {$sizeFormatted}");

        return 0;
    }

    /**
     * Format bytes to human-readable size
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
