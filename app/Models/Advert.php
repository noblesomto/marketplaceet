<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class Advert extends Model implements HasMedia
{
    use HasFactory;
    use Sluggable;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'idempotency_key',
        'ad_id',
        'ad_type',
        'ad_title',
        'title_slug',
        'category',
        'sub_category',
        'brand',
        'price',
        'contact_price',
        'salary',
        'expected_salary',
        'quantity',
        'price_type',
        'item_condition',
        'buy_direct',
        'description',
        'state',
        'lga',
        'state_slug',
        'ad_status',
        'shipment',
        'shipping',
        'keyword',
        'meta_description',
        'views',
        'featured',
        'sold',
        'sold_date',
        'resubmitted_at',
        'show_contact',
        'source',
    ];


    protected function casts(): array
    {
        return [
            'resubmitted_at' => 'datetime',
        ];
    }

    public function sluggable(): array
    {
        return [
            'title_slug' => [
                'source' => 'ad_title'
            ]
        ];
    }

    public function images()
    {
        return $this->hasMany(AdvertImage::class, 'advert_id')->orderBy('position');
    }

    /**
     * Cover image for listing cards. Uses the eager-loaded firstImage relation
     * when the query loaded it (one query for the whole list) instead of
     * getFirstMedia(), which loads every ad's media separately.
     */
    public function cardImage(): ?Media
    {
        if ($this->relationLoaded('firstImage')) {
            return $this->firstImage;
        }

        return $this->getFirstMedia('images');
    }

    public function firstImage()
    {
        return $this->hasOne(Media::class, 'model_id')
            ->where('model_type', self::class)
            ->where('collection_name', 'images')
            ->orderBy('order_column');
    }

    public function brands()
    {
        return $this->belongsTo(Brands::class, 'brand');
    }
    public function phone()
    {
        return $this->hasOne(PhoneDetail::class, 'advert_id');
    }

    public function computer()
    {
        return $this->hasOne(ComputerDetail::class, 'advert_id');
    }

    public function car()
    {
        return $this->hasOne(CarDetail::class, 'advert_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'advert_id');
    }

    public function boost()
    {
        return $this->hasMany(AdvertBoost::class, 'advert_id');
    }

    public function reports()
    {
        return $this->hasMany(Reports::class, 'advert_id');
    }

    public function moderationLogs()
    {
        return $this->hasMany(AdvertModerationLog::class)->latest();
    }

    // Most recent ban/rejection reason — null once the advert has never been
    // banned, or after it's been through a rejection cycle with no new ban since.
    public function latestModerationReason()
    {
        return $this->moderationLogs()
            ->whereIn('action', ['banned', 'rejected'])
            ->first();
    }

    public function shippings()
    {
        return $this->belongsToMany(Shipping::class, 'advert_shipping');
    }

    public function payment()
    {
        return $this->hasMany(Payment::class, 'advert_id');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'advert_id');
    }

    public function scopeActiveNotRecentlySold($query)
    {
        return $query->where('ad_status', 'active')
            ->where(function ($q) {
                $q->where('sold', '!=', 'Yes')
                  ->orWhere(function ($q) {
                      $q->where('sold', 'Yes')
                        ->whereNotNull('sold_date')
                        ->where('sold_date', '>=', now()->subDays(30));
                  });
            });
    }

    /**
     * Categories whose listings have models, and the detail table holding
     * each listing's model (models.id): Vehicles → car_details, Mobile Phones
     * & Tablets → phone_details, Electronics (Laptops & Computers) →
     * computer_details.
     */
    public const MODEL_DETAIL_TABLES = [
        1 => 'car_details',
        4 => 'phone_details',
        2 => 'computer_details',
    ];

    /** Same as MODEL_DETAIL_TABLES, as relation names. */
    public const MODEL_DETAIL_RELATIONS = [
        1 => 'car',
        4 => 'phone',
        2 => 'computer',
    ];

    /**
     * Adverts for a given model id, or any of a list of ids (models.id).
     * Models hang off the detail tables — see MODEL_DETAIL_TABLES.
     */
    public function scopeOfModel($query, $modelIds)
    {
        $modelIds = (array) $modelIds;

        return $query->where(function ($q) use ($modelIds) {
            $q->whereHas('phone', fn ($d) => $d->whereIn('model', $modelIds))
              ->orWhereHas('car', fn ($d) => $d->whereIn('model', $modelIds))
              ->orWhereHas('computer', fn ($d) => $d->whereIn('model', $modelIds));
        });
    }

    /**
     * Give a newly created advert its public ad_id (used in its URL): its own
     * id, or — if an older advert already holds that number — the next free
     * one after the highest ad_id. (New ids start past old ad_ids; see the
     * 2026_10_26 auto-increment migration.)
     */
    public function assignAdId(): void
    {
        $adId = (string) $this->id;

        if (self::where('ad_id', $adId)->where('id', '!=', $this->id)->exists()) {
            $adId = (string) (max($this->id, (int) self::query()->selectRaw('MAX(CAST(ad_id AS UNSIGNED)) AS mx')->value('mx')) + 1);
        }

        $this->update(['ad_id' => $adId]);
    }

    /**
     * This listing's model id (models.id) from whichever detail table holds it.
     */
    public function detailModelId(): ?int
    {
        $model = optional($this->car)->model ?? optional($this->phone)->model ?? optional($this->computer)->model;

        return is_numeric($model) && (int) $model > 0 ? (int) $model : null;
    }

    public function scopeFeaturedBoosted($query)
    {
        return $query->where('featured', 'Yes')
            ->where('sold', 'No')
            ->where('ad_status', 'active')
            ->whereHas('boost', function ($q) {
                $q->where('boost_status', 'active');
            });
    }

    protected static function boot()
    {
        parent::boot();

        // Automatically handle media when deleting the advert
        static::deleting(function ($advert) {
            // Special handling for Job category (category 3)
            if ($advert->category == 3) {
                // For Job category, only delete non-default images
                $media = $advert->getMedia('images');
                foreach ($media as $mediaItem) {
                    // Only delete if it's not a default image
                    if (!$mediaItem->getCustomProperty('is_default', false)) {
                        $mediaItem->delete();
                    }
                }
            } else {
                // For all other categories, clear all images
                $advert->clearMediaCollection('images');
            }

        });
    }

    public function scopeOrderWithFeatured($query)
    {
        return $query->selectRaw('adverts.*, (featured = "Yes") as is_featured')
                     ->orderByDesc('is_featured')  // Featured first
                     ->orderByDesc('created_at');  // Then newest
    }

    public function scopeActiveNotSold($query)
    {
        return $query->where('ad_status', 'active')
            ->where(function($q) {
                $q->where('sold', '!=', 'Yes')
                  ->orWhere(function($subQ) {
                      $subQ->where('sold', 'Yes')
                           ->whereNotNull('sold_date')
                           ->where('sold_date', '>=', now()->subDays(30));
                  });
            });
    }

    // Active, non-sold ads that are NOT currently featured/boosted.
    // Used for main listing pagination — featured ads are pulled separately
    // so they don't double-count in the total or appear twice on page 1.
    public function scopeRegularAds($query)
    {
        return $query->where('ad_status', 'active')
            ->where(function ($q) {
                $q->where('sold', '!=', 'Yes')
                  ->orWhere(function ($subQ) {
                      $subQ->where('sold', 'Yes')
                           ->whereNotNull('sold_date')
                           ->where('sold_date', '>=', now()->subDays(30));
                  });
            })
            ->where(function ($q) {
                $q->where('featured', '!=', 'Yes')
                  ->orWhereDoesntHave('boost', function ($bq) {
                      $bq->where('boost_status', 'active');
                  });
            });
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Optimized version - full-size for detail/gallery pages
        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(80)
            ->width(1600)
            ->fit(Fit::Max)
            ->performOnCollections('images')
            ->nonQueued();

        // Large – social sharing, detail pages
        $this->addMediaConversion('large')
            ->format('webp')
            ->quality(80)
            ->width(1200)
            ->fit(Fit::Max)
            ->performOnCollections('images')
            ->nonQueued();

        // Square thumbnail – fallback
        $this->addMediaConversion('thumbnail')
            ->format('webp')
            ->quality(80)
            ->width(400)
            ->height(400)
            ->fit(Fit::Crop)
            ->performOnCollections('images')
            ->nonQueued();

        // Mobile card – 2× resolution for HiDPI screens (displayed at ~300×225)
        $this->addMediaConversion('thumb-sm')
            ->format('webp')
            ->quality(80)
            ->width(600)
            ->height(450)
            ->fit(Fit::Crop)
            ->performOnCollections('images')
            ->nonQueued();

        // Desktop card – 2× resolution for HiDPI screens (displayed at ~400×300)
        $this->addMediaConversion('thumb-md')
            ->format('webp')
            ->quality(80)
            ->width(800)
            ->height(600)
            ->fit(Fit::Crop)
            ->performOnCollections('images')
            ->nonQueued();


    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes([
                'image/jpeg', 'image/png', 'image/gif',
                'image/webp', 'image/bmp', 'image/tiff',
                'image/heic', 'image/heif'
            ]);
    }

    public function getImages()
    {
        return $this->getMedia('images');
    }

    public function getFirstImageUrl($conversion = 'optimized')
    {
        $media = $this->getFirstMedia('images');
        return $media ? $media->getUrl($conversion) : null;
    }

    public function getFirstImage()
    {
        return $this->getFirstMedia('images');
    }

    public function getAllImageUrls($conversion = 'optimized')
    {
        return $this->getMedia('images')->map(function ($media) use ($conversion) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl($conversion),
                'thumbnail' => $media->getUrl('thumbnail'),
                'original' => $media->getUrl(),
                'order' => $media->getCustomProperty('position', $media->order_column)
            ];
        })->sortBy('order')->values();
    }

    /**
     * Add default image for specific categories
     */
    public function addDefaultImage($imageName = 'jobs.png')
    {
        $imagePath = public_path('images/' . $imageName); // Adjust path as needed

        if (file_exists($imagePath)) {
            $this->addMedia($imagePath)
                ->preservingOriginal()
                ->withCustomProperties(['position' => 1])
                ->usingName($imageName)
                ->usingFileName($imageName)
                ->toMediaCollection('images');
        }
    }

    /**
 * Get social media optimized image URL (absolute URL)
 * Uses existing 'large' conversion (1200px, WebP)
 */
public function getSocialImageUrl()
{
    if ($this->hasMedia('images')) {
        $media = $this->getFirstMedia('images');

        // Use large conversion for social media
        if ($media->hasGeneratedConversion('large')) {
            return $media->getFullUrl('large');
        }

        // Fallback to optimized, then original
        if ($media->hasGeneratedConversion('optimized')) {
            return $media->getFullUrl('optimized');
        }

        return $media->getFullUrl();
    }

    // Return default image with full URL
    return url('frontend/images/og-default.png');
}

/**
 * Get all images as absolute URLs for schema markup
 */
public function getAllImagesForSchema()
{
    if ($this->hasMedia('images')) {
        return $this->getMedia('images')->map(function ($media) {
            // Use large conversion for schema
            if ($media->hasGeneratedConversion('large')) {
                return $media->getFullUrl('large');
            }
            return $media->getFullUrl();
        })->toArray();
    }

    return [url('frontend/images/og-default.png')];
}

/**
 * Get image dimensions for social sharing (approximate for large conversion)
 */
public function getSocialImageDimensions()
{
    return [
        'width' => 1200,
        'height' => 630 // Approximate, actual depends on original aspect ratio
    ];
}

public function getCleanTitleAttribute()
{
    $title = $this->ad_title ?? '';
    $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $title = strip_tags($title);
    $title = preg_replace('/\s+/', ' ', $title);
    return trim($title);
}

public function getCleanDescriptionAttribute()
{
    // Use meta_description if available, otherwise fall back to description
    $description = $this->meta_description ?? $this->description ?? '';

    // Clean the text
    $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $description = strip_tags($description);
    $description = preg_replace('/\s+/', ' ', $description);

    return trim($description);
}

    protected $appends = ['image_url', 'thumbnail_url', 'display_image_url'];

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('images');
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->getFirstMediaUrl('images', 'thumbnail');
    }

    // 800×600 landscape WebP — use this for detail/boost/payment screens in the mobile app
    public function getDisplayImageUrlAttribute()
    {
        $url = $this->getFirstMediaUrl('images', 'thumb-md');
        return $url ?: $this->getFirstMediaUrl('images');
    }


}
