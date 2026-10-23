<?php
namespace App\Services;

use Illuminate\Http\Request;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaImageService
{
    /**
     * Handle image uploads for any model
     *
     * @param Request $request
     * @param HasMedia $model
     * @param string $collection
     * @param bool $clearExisting
     * @return array
     */
    public function handleImageUploads(
        Request $request,
        HasMedia $model,
        string $collection = 'images',
        bool $clearExisting = false
    ): array {
        $uploadedMedia = [];

        if (!$request->hasFile('images')) {
            return $uploadedMedia;
        }

        // Clear existing media if requested
        if ($clearExisting) {
            $model->clearMediaCollection($collection);
        }

        $images = array_values($request->file('images'));
        $order = explode(',', $request->input('image_order', ''));

        // If no order specified, use array indices
        if (empty($order) || count($order) != count($images)) {
            $order = array_keys($images);
        }

        foreach ($order as $position => $index) {
            // Skip if index doesn't exist or file is invalid
            if (!isset($images[$index]) || !$images[$index]->isValid()) {
                continue;
            }

            try {
                // Add media with position as custom property
                $media = $model
                    ->addMedia($images[$index])
                    ->withCustomProperties([
                        'position' => $position + 1,
                        'original_name' => $images[$index]->getClientOriginalName()
                    ])
                    ->usingFileName(uniqid() . '.webp')
                    ->toMediaCollection($collection);

                // Set the order_column for sorting (Spatie's built-in ordering)
                $media->order_column = $position + 1;
                $media->save();

                // Delete original file after conversions are created
                $this->deleteOriginalAfterConversions($media);

                $uploadedMedia[] = $media;

            } catch (\Exception $e) {
                \Log::error("Failed to upload image for {$model->getMorphClass()} ID {$model->id}: " . $e->getMessage());
                continue;
            }
        }

        return $uploadedMedia;
    }

    /**
     * Delete original file after conversions are created to save space
     *
     * @param Media $media
     * @return void
     */
    protected function deleteOriginalAfterConversions(Media $media): void
    {
        try {
            // Wait a moment for conversions to be created (if not queued)
            sleep(1);

            // Delete the original file but keep the database record
            $originalPath = $media->getPath();
            if (file_exists($originalPath)) {
                unlink($originalPath);
                \Log::info("Deleted original file to save space: {$originalPath}");
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to delete original file for media ID {$media->id}: " . $e->getMessage());
        }
    }

    // ... rest of your existing methods remain the same ...

    /**
     * Reorder images for any model
     *
     * @param HasMedia $model
     * @param array $imageIds
     * @param string $collection
     * @return bool
     */
    public function reorderImages(HasMedia $model, array $imageIds, string $collection = 'images'): bool
    {
        try {
            foreach ($imageIds as $position => $mediaId) {
                $media = $model->getMedia($collection)->where('id', $mediaId)->first();
                if ($media) {
                    $media->order_column = $position + 1;
                    $media->setCustomProperty('position', $position + 1);
                    $media->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to reorder images for {$model->getMorphClass()} ID {$model->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete specific image
     *
     * @param HasMedia $model
     * @param int $mediaId
     * @param string $collection
     * @param bool $reorderRemaining
     * @return bool
     */
    public function deleteImage(
        HasMedia $model,
        int $mediaId,
        string $collection = 'images',
        bool $reorderRemaining = true
    ): bool {
        try {
            // Try multiple ways to find the media record
            $media = $model->getMedia($collection)->where('id', $mediaId)->first();

            // If not found in the collection, try finding it directly
            if (!$media) {
                $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);

                // Verify it belongs to this model and collection
                if (!$media || $media->model_id != $model->id || $media->model_type != get_class($model) || $media->collection_name != $collection) {
                    \Log::warning("Media ID {$mediaId} not found or doesn't belong to model {$model->getMorphClass()} ID {$model->id} in collection '{$collection}'");
                    return false;
                }
            }

            \Log::info("Deleting media ID {$mediaId}: {$media->name} from collection '{$collection}'");

            // Delete the media record (this should also delete the files)
            $deleted = $media->delete();

            if (!$deleted) {
                \Log::error("Failed to delete media record ID {$mediaId}");
                return false;
            }

            \Log::info("Successfully deleted media ID {$mediaId}");

            // Reorder remaining images if requested
            if ($reorderRemaining) {
                $this->reorderRemainingImages($model, $collection);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to delete image for {$model->getMorphClass()} ID {$model->id}: " . $e->getMessage(), [
                'media_id' => $mediaId,
                'collection' => $collection,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Delete multiple images by IDs - DETAILED EXPLANATION
     *
     * @param HasMedia $model - The model that owns the images (e.g., your Advert)
     * @param array $mediaIds - Array of media IDs to delete [1, 3, 5, 8]
     * @param string $collection - Media collection name (default: 'images')
     * @param bool $reorderRemaining - Whether to reorder remaining images after deletion
     * @return array - Returns results of the operation
     */
    public function deleteMultipleImages(
        HasMedia $model,
        array $mediaIds,
        string $collection = 'images',
        bool $reorderRemaining = true
    ): array {
        $deleted = 0;
        $errors = [];

        // Loop through each media ID and try to delete it
        foreach ($mediaIds as $mediaId) {
            // Use the existing deleteImage method for each ID
            if ($this->deleteImage($model, $mediaId, $collection, false)) {
                $deleted++; // Count successful deletions
            } else {
                $errors[] = "Failed to delete image ID: {$mediaId}";
            }
        }

        // After deleting multiple images, reorder the remaining ones
        // so there are no gaps in positions (1, 2, 3, 4 instead of 1, 3, 6, 8)
        if ($reorderRemaining && $deleted > 0) {
            $this->reorderRemainingImages($model, $collection);
        }

        return [
            'deleted' => $deleted,    // How many were successfully deleted
            'errors' => $errors       // Array of any errors that occurred
        ];
    }

    /**
     * Validate that deleting the given media IDs would not drop the model's image
     * count below $minImages, then perform the deletion. This is the single
     * authoritative guard for "advert must keep at least N images" — it must be
     * used by every caller that lets a user delete advert images (web, API, admin).
     *
     * Counts are read via the model's `media()` relation query (a fresh DB query)
     * rather than the `getMedia()`/`$model->media` cached relation, because that
     * cache is not invalidated when a related Media row is deleted through a
     * different reference — a stale cache would make a "final count" check performed
     * after deletion silently see the pre-deletion count forever. The cached
     * relation is explicitly unset afterward so any later getMedia() call in the
     * same request (reordering, response building, etc.) reflects the true state.
     *
     * Deletion only ever happens when the resulting count is provably >= $minImages,
     * so this never needs to be "undone" - there is no reliance on DB transaction
     * rollback, which cannot undo the media files Spatie deletes from disk
     * synchronously as part of Media::delete().
     *
     * @param HasMedia $model
     * @param array $mediaIdsToDelete
     * @param int $minImages
     * @param string $collection
     * @return array{success: bool, deleted: int, error?: string}
     */
    public function validateAndDeleteImages(
        HasMedia $model,
        array $mediaIdsToDelete,
        int $minImages,
        string $collection = 'images'
    ): array {
        $mediaIdsToDelete = array_values(array_unique(array_filter(array_map('intval', $mediaIdsToDelete))));

        if (empty($mediaIdsToDelete)) {
            return ['success' => true, 'deleted' => 0];
        }

        $collectionQuery = fn () => $model->media()->where('collection_name', $collection);

        $currentCount = $collectionQuery()->count();
        $ownedIdsToDelete = $collectionQuery()->whereIn('id', $mediaIdsToDelete)->pluck('id')->all();

        $remaining = $currentCount - count($ownedIdsToDelete);

        if ($remaining < $minImages) {
            \Log::info("Blocked image deletion for {$model->getMorphClass()} ID {$model->id}: would leave {$remaining} image(s), minimum is {$minImages}");

            return [
                'success' => false,
                'deleted' => 0,
                'error'   => "Cannot delete those images. Adverts must have at least {$minImages} image" . ($minImages === 1 ? '' : 's') . ".",
            ];
        }

        $deleted = 0;
        foreach ($ownedIdsToDelete as $mediaId) {
            $media = Media::find($mediaId);
            if ($media) {
                $media->delete();
                $deleted++;
            }
        }

        $model->unsetRelation('media');

        \Log::info("Deleted {$deleted} image(s) for {$model->getMorphClass()} ID {$model->id}, collection '{$collection}'");

        return ['success' => true, 'deleted' => $deleted];
    }

    /**
     * Delete all images from collection
     *
     * @param HasMedia $model
     * @param string $collection
     * @return bool
     */
    public function clearImages(HasMedia $model, string $collection = 'images'): bool
    {
        try {
            $model->clearMediaCollection($collection);
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to clear images for {$model->getMorphClass()} ID {$model->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all image URLs with different conversions
     *
     * @param HasMedia $model
     * @param string $collection
     * @param array $conversions
     * @return array
     */
    public function getImageUrls(
        HasMedia $model,
        string $collection = 'images',
        array $conversions = ['optimized', 'thumbnail']
    ): array {
        return $model->getMedia($collection)->map(function ($media) use ($conversions) {
            $urls = [
                'id' => $media->id,
                'original' => $this->getOriginalOrFallback($media),
                'position' => $media->getCustomProperty('position', $media->order_column),
                'name' => $media->getCustomProperty('original_name', $media->name),
            ];

            foreach ($conversions as $conversion) {
                $urls[$conversion] = $media->getUrl($conversion);
            }

            return $urls;
        })->sortBy('position')->values()->toArray();
    }

    /**
     * Get original URL or fallback to large conversion if original was deleted
     *
     * @param Media $media
     * @return string
     */
    protected function getOriginalOrFallback(Media $media): string
    {
        try {
            $originalUrl = $media->getUrl();
            $originalPath = $media->getPath();

            // If original file doesn't exist, return large conversion as fallback
            if (!file_exists($originalPath)) {
                return $media->getUrl('large');
            }

            return $originalUrl;
        } catch (\Exception $e) {
            // Fallback to large conversion
            return $media->getUrl('large');
        }
    }

    /**
     * Get first image URL
     *
     * @param HasMedia $model
     * @param string $collection
     * @param string $conversion
     * @return string|null
     */
    public function getFirstImageUrl(
        HasMedia $model,
        string $collection = 'images',
        string $conversion = 'optimized'
    ): ?string {
        $media = $model->getFirstMedia($collection);
        return $media ? $media->getUrl($conversion) : null;
    }

    /**
     * Add default image
     *
     * @param HasMedia $model
     * @param string $imagePath
     * @param string $collection
     * @param array $customProperties
     * @return Media|null
     */
    public function addDefaultImage(
        HasMedia $model,
        string $imagePath,
        string $collection = 'images',
        array $customProperties = []
    ): ?Media {
        if (!file_exists($imagePath)) {
            \Log::warning("Default image not found: {$imagePath}");
            return null;
        }

        try {
            $defaultProperties = array_merge(['position' => 1], $customProperties);

            $media = $model
                ->addMedia($imagePath)
                ->withCustomProperties($defaultProperties)
                ->usingName(basename($imagePath))
                ->usingFileName(basename($imagePath))
                ->toMediaCollection($collection);

            $media->order_column = $defaultProperties['position'];
            $media->save();

            return $media;
        } catch (\Exception $e) {
            \Log::error("Failed to add default image for {$model->getMorphClass()} ID {$model->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Reorder remaining images after deletion
     *
     * @param HasMedia $model
     * @param string $collection
     * @return void
     */
    protected function reorderRemainingImages(HasMedia $model, string $collection = 'images'): void
    {
        $images = $model->getMedia($collection)->sortBy('order_column');

        foreach ($images as $index => $media) {
            $media->order_column = $index + 1;
            $media->setCustomProperty('position', $index + 1);
            $media->save();
        }
    }

    /**
     * Replace all images (useful for updates)
     *
     * @param Request $request
     * @param HasMedia $model
     * @param string $collection
     * @return array
     */
    public function replaceAllImages(
        Request $request,
        HasMedia $model,
        string $collection = 'images'
    ): array {
        return $this->handleImageUploads($request, $model, $collection, true);
    }

    /**
     * Force delete media record and files (use when normal delete fails)
     *
     * @param int $mediaId
     * @return bool
     */
    public function forceDeleteMedia(int $mediaId): bool
{
    try {
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);

        if (!$media) {
            \Log::warning("Media ID {$mediaId} not found in database");
            return false;
        }

        \Log::info("Force deleting media ID {$mediaId}: {$media->name}");

        $media->forceDelete(); // this removes DB + files

        return true;
    } catch (\Exception $e) {
        \Log::error("Exception during force delete of media ID {$mediaId}: " . $e->getMessage());
        return false;
    }
}


    /**
     * Clean up orphaned media records (records without files or files without records)
     *
     * @param HasMedia $model
     * @param string $collection
     * @return array
     */
    public function cleanupOrphanedMedia(HasMedia $model, string $collection = 'images'): array
{
    $cleaned = [
        'deleted_records' => 0,
        'errors' => []
    ];

    try {
        $mediaItems = $model->getMedia($collection);

        foreach ($mediaItems as $media) {
            // If original and conversions don’t exist → delete the media completely
            if (!file_exists($media->getPath()) &&
                !file_exists($media->getPath('thumbnail')) &&
                !file_exists($media->getPath('optimized')) &&
                !file_exists($media->getPath('large'))) {

                try {
                    $media->forceDelete(); // DB + any leftover files
                    $cleaned['deleted_records']++;
                    \Log::info("Cleaned orphaned media ID {$media->id}");
                } catch (\Exception $e) {
                    $cleaned['errors'][] = "Failed to delete orphaned record ID {$media->id}";
                }
            }
        }

    } catch (\Exception $e) {
        $cleaned['errors'][] = "Exception during cleanup: " . $e->getMessage();
        \Log::error("Cleanup exception: " . $e->getMessage());
    }

    return $cleaned;
}


    /**
     * Check if model has images
     *
     * @param HasMedia $model
     * @param string $collection
     * @return bool
     */
    public function hasImages(HasMedia $model, string $collection = 'images'): bool
    {
        return $model->hasMedia($collection);
    }

    /**
     * Clean up existing original files to save space (run this as a command)
     *
     * @param HasMedia $model
     * @param string $collection
     * @return int Number of files deleted
     */
    public function cleanupOriginalFiles(HasMedia $model, string $collection = 'images'): int
    {
        $deleted = 0;
        $media = $model->getMedia($collection);

        foreach ($media as $mediaItem) {
            try {
                $originalPath = $mediaItem->getPath();
                if (file_exists($originalPath)) {
                    // Verify that conversions exist before deleting original
                    $largeExists = file_exists($mediaItem->getPath('large'));
                    $optimizedExists = file_exists($mediaItem->getPath('optimized'));

                    if ($largeExists && $optimizedExists) {
                        unlink($originalPath);
                        $deleted++;
                        \Log::info("Cleaned up original file: {$originalPath}");
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to cleanup original file for media ID {$mediaItem->id}: " . $e->getMessage());
            }
        }

        return $deleted;
    }


}
