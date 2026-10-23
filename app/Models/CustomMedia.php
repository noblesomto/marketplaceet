<?php
namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class CustomMedia extends BaseMedia
{
    public function registerMediaConversions(Media $media = null): void
    {
        parent::registerMediaConversions($media);

        // After all conversions, delete the original
        if ($this->collection_name === 'message_images') {
            $this->addMediaConversion('cleanup')
                ->performOnCollections('message_images')
                ->afterConversionComplete(function (Media $media) {
                    $originalPath = $media->getPath();
                    if (file_exists($originalPath) && $media->hasGeneratedConversion('webp')) {
                        unlink($originalPath);
                    }
                });
        }
    }
}
