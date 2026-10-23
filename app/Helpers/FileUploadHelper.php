<?php
namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\Image\Enums\Fit;

class FileUploadHelper
{
    /**
     * Upload and process file using Spatie Media Library
     *
     * @param UploadedFile $file
     * @param HasMedia     $model
     * @param string       $collection
     * @param string|null  $conversionName
     * @return Media|null
     */
    public static function upload(
        UploadedFile $file,
        HasMedia $model,
        string $collection = 'default',
        string $conversionName = 'optimized'
    ): ?Media {
        try {
            // Delete old media if exists
            $model->clearMediaCollection($collection);

            // Add file to media collection
            $media = $model
                ->addMediaFromRequest('file')
                ->usingFileName(uniqid() . '.webp')
                ->toMediaCollection($collection);

            return $media;
        } catch (\Exception $e) {
            throw new \Exception("Failed to upload file: " . $e->getMessage());
        }
    }

    /**
     * Upload file from path
     *
     * @param string   $filePath
     * @param HasMedia $model
     * @param string   $collection
     * @return Media|null
     */
    public static function uploadFromPath(
        string $filePath,
        HasMedia $model,
        string $collection = 'default'
    ): ?Media {
        try {
            $model->clearMediaCollection($collection);

            $media = $model
                ->addMedia($filePath)
                ->usingFileName(uniqid() . '.webp')
                ->toMediaCollection($collection);

            return $media;
        } catch (\Exception $e) {
            throw new \Exception("Failed to upload file from path: " . $e->getMessage());
        }
    }

    /**
     * Delete media from collection
     *
     * @param HasMedia $model
     * @param string   $collection
     * @return bool
     */
    public static function delete(HasMedia $model, string $collection = 'default'): bool
    {
        try {
            $model->clearMediaCollection($collection);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete specific media item
     *
     * @param Media $media
     * @return bool
     */
    public static function deleteMedia(Media $media): bool
    {
        try {
            $media->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get media URL with conversion
     *
     * @param HasMedia    $model
     * @param string      $collection
     * @param string|null $conversion
     * @return string|null
     */
    public static function getMediaUrl(
        HasMedia $model,
        string $collection = 'default',
        string $conversion = null
    ): ?string {
        $media = $model->getFirstMedia($collection);

        if (!$media) {
            return null;
        }

        return $conversion ? $media->getUrl($conversion) : $media->getUrl();
    }

    /**
     * Check if model has media in collection
     *
     * @param HasMedia $model
     * @param string   $collection
     * @return bool
     */
    public static function hasMedia(HasMedia $model, string $collection = 'default'): bool
    {
        return $model->hasMedia($collection);
    }
}

// Example Model that uses media library
// You would add this trait and method to your existing models

trait HasMediaTrait
{
    use InteractsWithMedia;

    /**
     * Register media conversions
     */
    public function registerMediaConversions(Media $media = null): void
    {
        // Optimized version - maintains aspect ratio, only resizes if larger
        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(65)
            ->optimize()
            ->nonQueued(); // Use queued() for better performance in production

        // Large version - max dimension constraint while preserving aspect ratio
        $this->addMediaConversion('large')
            ->format('webp')
            ->quality(70)
            ->width(1200)
            ->height(1200)
            ->fit(Fit::Max) // Maintains aspect ratio, won't exceed either dimension
            ->optimize()
            ->nonQueued();

        // Medium version - responsive sizing
        $this->addMediaConversion('medium')
            ->format('webp')
            ->quality(65)
            ->width(800)
            ->height(800)
            ->fit(Fit::Max)
            ->optimize()
            ->nonQueued();

        // Thumbnail - square crop for consistency in grids
        $this->addMediaConversion('thumbnail')
            ->format('webp')
            ->quality(70)
            ->width(300)
            ->height(300)
            ->fit(Fit::Crop)
            ->optimize()
            ->nonQueued();

        // Small thumbnail
        $this->addMediaConversion('small')
            ->format('webp')
            ->quality(60)
            ->width(150)
            ->height(150)
            ->fit(Fit::Crop)
            ->optimize()
            ->nonQueued();
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/tiff'])
            ->singleFile();

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
            ->singleFile();

        $this->addMediaCollection('gallery');
    }
}
