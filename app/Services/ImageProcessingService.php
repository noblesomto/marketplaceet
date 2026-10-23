<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImageProcessingService
{
    /**
     * Process and compress image to WebP format
     *
     * @param UploadedFile $file
     * @param array $options
     * @return string Path to compressed image
     */
    public static function processAndCompress(UploadedFile $file, array $options = []): string
    {
        // Default options
        $defaultOptions = [
            'max_width' => 1200,
            'max_height' => 1600,
            'quality' => 60,
            'format' => 'webp'
        ];

        $options = array_merge($defaultOptions, $options);

        // Create a temporary file path for the compressed image
        $extension = $options['format'] === 'webp' ? 'webp' : $file->getClientOriginalExtension();
        $tempPath = sys_get_temp_dir() . '/' . uniqid('compressed_') . '.' . $extension;

        try {
            // Get original image dimensions
            $imageInfo = getimagesize($file->getRealPath());
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];

            // Calculate new dimensions
            $newDimensions = self::calculateNewDimensions(
                $originalWidth,
                $originalHeight,
                $options['max_width'],
                $options['max_height']
            );

            // Create image resource based on original format
            $sourceImage = self::createImageResource($file);

            // Create new resized image
            $resizedImage = imagecreatetruecolor($newDimensions['width'], $newDimensions['height']);

            // Handle transparency for PNG/GIF
            if (in_array($file->getMimeType(), ['image/png', 'image/gif'])) {
                self::handleTransparency($resizedImage, $newDimensions['width'], $newDimensions['height']);
            }

            // Resize the image
            imagecopyresampled(
                $resizedImage, $sourceImage,
                0, 0, 0, 0,
                $newDimensions['width'], $newDimensions['height'],
                $originalWidth, $originalHeight
            );

            // Save in specified format
            self::saveImage($resizedImage, $tempPath, $options['format'], $options['quality']);

            // Clean up memory
            imagedestroy($sourceImage);
            imagedestroy($resizedImage);

            return $tempPath;

        } catch (\Exception $e) {
            Log::warning('Image compression failed: ' . $e->getMessage());

            // Fall back to original file
            $fallbackPath = sys_get_temp_dir() . '/' . uniqid('fallback_') . '.' . $file->getClientOriginalExtension();
            copy($file->getRealPath(), $fallbackPath);
            return $fallbackPath;
        }
    }

    /**
     * Process image specifically for profile pictures
     */
    public static function processProfileImage(UploadedFile $file): string
    {
        return self::processAndCompress($file, [
            'max_width' => 800,
            'max_height' => 800,
            'quality' => 75,
            'format' => 'webp'
        ]);
    }

    /**
     * Process image for document verification
     */
    public static function processDocumentImage(UploadedFile $file): string
    {
        return self::processAndCompress($file, [
            'max_width' => 1200,
            'max_height' => 1600,
            'quality' => 60,
            'format' => 'webp'
        ]);
    }

    /**
     * Process image for thumbnails
     */
    public static function processThumbnail(UploadedFile $file): string
    {
        return self::processAndCompress($file, [
            'max_width' => 300,
            'max_height' => 300,
            'quality' => 50,
            'format' => 'webp'
        ]);
    }

    /**
     * Calculate new dimensions maintaining aspect ratio
     */
    private static function calculateNewDimensions(int $originalWidth, int $originalHeight, int $maxWidth, int $maxHeight): array
    {
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return ['width' => $originalWidth, 'height' => $originalHeight];
        }

        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);

        return [
            'width' => round($originalWidth * $ratio),
            'height' => round($originalHeight * $ratio)
        ];
    }

    /**
     * Create image resource from uploaded file
     */
    private static function createImageResource(UploadedFile $file)
    {
        return match($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/gif' => imagecreatefromgif($file->getRealPath()),
            default => imagecreatefromjpeg($file->getRealPath())
        };
    }

    /**
     * Handle transparency for PNG/GIF images
     */
    private static function handleTransparency($image, int $width, int $height): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefilledrectangle($image, 0, 0, $width, $height, $transparent);
    }

    /**
     * Save image in specified format
     */
    private static function saveImage($image, string $path, string $format, int $quality): void
    {
        match($format) {
            'webp' => imagewebp($image, $path, $quality),
            'jpeg', 'jpg' => imagejpeg($image, $path, $quality),
            'png' => imagepng($image, $path, round(9 * (100 - $quality) / 100)),
            default => imagewebp($image, $path, $quality)
        };
    }

    /**
     * Generate optimized filename
     */
    public static function generateFileName(UploadedFile $file, string $prefix = '', string $format = 'webp'): string
    {
        $timestamp = now()->format('YmdHis');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanName = preg_replace('/[^A-Za-z0-9\-_]/', '', $originalName);

        $prefix = $prefix ? $prefix . '_' : '';
        $extension = $format === 'webp' ? 'webp' : $file->getClientOriginalExtension();

        return "{$prefix}{$timestamp}_{$cleanName}.{$extension}";
    }

    /**
     * Clean up temporary file
     */
    public static function cleanupTempFile(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
