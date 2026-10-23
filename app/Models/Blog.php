<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class Blog extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['title', 'slug','category' ,'content', 'status', 'keywords','meta_description'];

    protected static function booted()
    {
        static::creating(function ($blog) {
            $blog->slug = Str::slug($blog->title);
        });

        static::deleting(function ($blog) {
            // Automatically remove all attached media before deleting
            $blog->clearMediaCollection('blog_featured_image');
        });
    }

    protected $appends = [
        'featured_image_thumb',
        'featured_image_url',
        'featured_image_webp',
    ];


    /**
     * Register media collections - unique to Blog model
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('blog_featured_image')
            ->useDisk('spatie')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);
    }

    /**
     * Register media conversions - specific to Blog
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('blog_thumb')
            ->format('webp')
            ->quality(70)
            ->width(300)
            ->height(300)
            ->fit(Fit::Crop)
            ->optimize()
            ->nonQueued()
            ->performOnCollections('blog_featured_image');

        $this->addMediaConversion('blog_large')
            ->format('webp')
            ->quality(70)
            ->width(1200)
            ->fit(Fit::Max)
            ->optimize()
            ->nonQueued()
            ->performOnCollections('blog_featured_image');

        $this->addMediaConversion('blog_webp')
            ->format('webp')
            ->quality(65)
            ->width(1600)
            ->fit(Fit::Max)
            ->optimize()
            ->nonQueued()
            ->performOnCollections('blog_featured_image');
    }

    /**
     * Get the featured image URL
     */
    public function getFeaturedImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('blog_featured_image', 'blog_large');
    }

    /**
     * Get the featured image thumbnail URL
     */
    public function getFeaturedImageThumbAttribute()
    {
        return $this->getFirstMediaUrl('blog_featured_image', 'blog_thumb');
    }


    /**
     * Get the WebP version
     */
    public function getFeaturedImageWebpAttribute()
    {
        return $this->getFirstMediaUrl('blog_featured_image', 'blog_webp');
    }
}
