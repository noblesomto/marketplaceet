<?php

namespace App\Listeners;

use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;

class DeleteOriginalAfterConversion
{
    public function handle(MediaHasBeenAdded $event)
    {
        $media = $event->media;

        // Only process profile_image collection
        if ($media->collection_name !== 'profile_image') {
            return;
        }

        // For non-queued conversions, wait a bit then delete
        // For queued conversions, you'd need a different approach
        if ($this->conversionsAreNonQueued()) {
            // Small delay to ensure conversions are processed
            sleep(2);

            $this->deleteOriginalFile($media);
        }
    }

    protected function conversionsAreNonQueued(): bool
    {
        // You can make this more sophisticated based on your configuration
        return true; // Assuming non-queued as per your code
    }

    protected function deleteOriginalFile($media): void
    {
        $disk = $media->getDisk();
        $originalPath = $media->getPath();

        if ($disk->exists($originalPath)) {
            // Check if at least one conversion exists
            $media->refresh();
            $conversions = $media->getGeneratedConversions();

            if (!empty($conversions)) {
                $disk->delete($originalPath);
                \Log::info("Original file deleted for media ID: {$media->id}");
            }
        }
    }
}
