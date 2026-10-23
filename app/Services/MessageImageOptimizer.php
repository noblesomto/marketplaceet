<?php
namespace App\Services;

use Spatie\Image\Image;
use Spatie\Image\Enums\Fit;

class MessageImageOptimizer
{
    public static function optimize(string $imagePath): void
    {
        $image = Image::load($imagePath);

        // Get original file size
        $originalSize = filesize($imagePath);
        $maxSize = 100 * 1024; // 100KB in bytes
        $quality = 85;

        // Convert to WebP and optimize
        $image->format('webp')
              ->quality($quality)
              ->optimize()
              ->save();

        // If still over 100KB, reduce quality iteratively
        while (filesize($imagePath) > $maxSize && $quality > 20) {
            $quality -= 5;
            $image->quality($quality)->save();
        }

        // If still too large, resize the image
        if (filesize($imagePath) > $maxSize) {
            $currentWidth = $image->getWidth();
            $newWidth = (int)($currentWidth * 0.9); // Reduce by 10%

            $image->fit(Fit::Max, $newWidth)
                  ->quality(75)
                  ->save();
        }
    }
}
