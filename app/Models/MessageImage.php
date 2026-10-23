<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Enums\Fit;

class MessageImage extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['message_id', 'image_path'];

    /**
     * Automatically include image URLs in JSON/API responses
     */
    protected $appends = ['optimized_image_url', 'large_image_url'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('message_images')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('large')
            ->format('webp')
            ->quality(70)
            ->width(1200)
            ->fit(Fit::Max)
            ->optimize()
            ->performOnCollections('message_images') // Fixed: was 'images'
            ->nonQueued();

        $this->addMediaConversion('thumbnail')
            ->format('webp')
            ->quality(70)
            ->width(300)
            ->height(300)
            ->fit(Fit::Crop)
            ->optimize()
            ->performOnCollections('message_images')
            ->nonQueued();

    }

    /**
     * This method is called after all conversions have been completed
     */
    public function conversionCompleted(Media $media): void
    {
        // Only delete for message_images collection
        if ($media->collection_name === 'message_images') {
            $originalPath = $media->getPath();

            // Check if thumbnail conversion exists (or any conversion)
            if ($media->hasGeneratedConversion('thumbnail') && file_exists($originalPath)) {
                @unlink($originalPath);
            }
        }
    }

    /**
     * Get the optimized image URL (thumbnail)
     */
    public function getOptimizedImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('message_images', 'thumbnail');
    }

    /**
     * Get the large image URL
     */
    public function getLargeImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('message_images', 'large');
    }

}
