<?php
namespace App\Traits;

use App\Services\MediaImageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\HasMedia;

trait ManagesImages
{
    protected MediaImageService $imageService;

    /**
     * Get the image service instance
     */
    protected function getImageService(): MediaImageService
    {
        if (!isset($this->imageService)) {
            $this->imageService = app(MediaImageService::class);
        }

        return $this->imageService;
    }

    /**
     * Handle image uploads for any model
     */
    protected function handleImageUploads(
        Request $request,
        HasMedia $model,
        string $collection = 'images',
        bool $clearExisting = false
    ): array {
        return $this->getImageService()->handleImageUploads($request, $model, $collection, $clearExisting);
    }

    /**
     * Reorder images - returns JSON response
     */
    protected function reorderImages(Request $request, HasMedia $model, string $collection = 'images'): JsonResponse
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'integer|exists:media,id'
        ]);

        $success = $this->getImageService()->reorderImages(
            $model,
            $request->input('image_ids'),
            $collection
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'images' => $this->getImageService()->getImageUrls($model, $collection, ['thumbnail', 'optimized'])
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to reorder images'
        ], 500);
    }

    /**
     * Delete specific image - returns JSON response
     */
    protected function deleteImage(HasMedia $model, int $mediaId, string $collection = 'images'): JsonResponse
    {
        $success = $this->getImageService()->deleteImage($model, $mediaId, $collection);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
                'images' => $this->getImageService()->getImageUrls($model, $collection, ['thumbnail', 'optimized'])
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Image not found or failed to delete'
        ], 404);
    }

    /**
     * Get all images for a model - returns JSON response
     */
    protected function getImages(
        HasMedia $model,
        string $collection = 'images',
        array $conversions = ['medium', 'thumbnail', 'optimized']
    ): JsonResponse {
        return response()->json([
            'images' => $this->getImageService()->getImageUrls($model, $collection, $conversions),
            'first_image' => $this->getImageService()->getFirstImageUrl($model, $collection, 'large'),
            'has_images' => $this->getImageService()->hasImages($model, $collection)
        ]);
    }

    /**
     * Clear all images from collection - returns JSON response
     */
    protected function clearImages(HasMedia $model, string $collection = 'images'): JsonResponse
    {
        $success = $this->getImageService()->clearImages($model, $collection);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'All images cleared successfully' : 'Failed to clear images'
        ]);
    }

    /**
     * Delete model with all its media - returns JSON response
     */
    protected function deleteWithImages(
        HasMedia $model,
        array $collections = ['images'],
        bool $forceDelete = false
    ): JsonResponse {
        try {
            // Clear all specified media collections
            foreach ($collections as $collection) {
                if ($model->hasMedia($collection)) {
                    $this->getImageService()->clearImages($model, $collection);
                }
            }

            // Delete the model
            if ($forceDelete) {
                $model->forceDelete();
            } else {
                $model->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Record and all media deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to delete {$model->getMorphClass()} ID {$model->id} with media: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete record with media'
            ], 500);
        }
    }

    /**
     * Soft delete model while keeping images (useful if you want to restore later)
     */
    protected function softDeleteKeepImages(HasMedia $model): JsonResponse
    {
        try {
            $model->delete(); // Only soft delete, keep images

            return response()->json([
                'success' => true,
                'message' => 'Record deleted (images preserved for potential restore)'
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to soft delete {$model->getMorphClass()} ID {$model->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete record'
            ], 500);
        }
    }
}
