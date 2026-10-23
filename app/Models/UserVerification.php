<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserVerification extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'document_number',
        'document_type',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($verification) {
            $verification->clearMediaCollection('verification_documents');
            $verification->clearMediaCollection('verification_address');
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('verification_documents')
            ->singleFile()
            ->onlyKeepLatest(1)
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/jpg',
                    'image/gif',
                    'image/webp',
                    'application/pdf',
                ]);
            })
            ->useFallbackUrl('/images/placeholder-document.png');

        $this->addMediaCollection('verification_address')
            ->singleFile()
            ->onlyKeepLatest(1)
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/jpg',
                    'image/gif',
                    'image/webp',
                    'application/pdf',
                ]);
            })
            ->useFallbackUrl('/images/placeholder-document.png');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (!$media || $media->mime_type !== 'application/pdf') {
            $this->addMediaConversion('optimized')
                ->format('webp')
                ->quality(65)
                ->optimize()
                ->performOnCollections('verification_documents', 'verification_address')
                ->nonQueued();

            $this->addMediaConversion('thumbnail')
                ->format('webp')
                ->quality(40)
                ->width(200)
                ->height(200)
                ->fit(Fit::Crop)
                ->optimize()
                ->performOnCollections('verification_documents', 'verification_address')
                ->nonQueued();
        }
    }



    /** ---------------------------
     *  Custom Accessors
     *  --------------------------*/

    public function getCompressedDocumentUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('verification_documents');
        if (!$media) return null;

        return $media->mime_type === 'application/pdf'
            ? $media->getUrl() // keep original PDF
            : $media->getUrl('optimized'); // optimized WebP
    }

    public function getCompressedAddressUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('verification_address');
        if (!$media) return null;

        return $media->mime_type === 'application/pdf'
            ? $media->getUrl()
            : $media->getUrl('optimized');
    }

    public function getDocumentThumbnailUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('verification_documents');
        if (!$media) return null;

        return $this->isImage($media)
            ? $media->getUrl('thumbnail')
            : $media->getUrl(); // fallback for PDFs
    }

    public function getAddressThumbnailUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('verification_address');
        if (!$media) return null;

        return $this->isImage($media)
            ? $media->getUrl('thumbnail')
            : $media->getUrl();
    }

    public function getDocumentSizeAttribute(): ?int
    {
        $media = $this->getFirstMedia('verification_documents');
        return $media ? $media->size : null;
    }

    public function getAddressSizeAttribute(): ?int
    {
        $media = $this->getFirstMedia('verification_address');
        return $media ? $media->size : null;
    }

    public function getIsDocumentOptimizedAttribute(): bool
    {
        $media = $this->getFirstMedia('verification_documents');
        if (!$media) return false;

        return $media->mime_type === 'image/webp' || $media->mime_type === 'application/pdf';
    }

    public function getIsAddressOptimizedAttribute(): bool
    {
        $media = $this->getFirstMedia('verification_address');
        if (!$media) return false;

        return $media->mime_type === 'image/webp' || $media->mime_type === 'application/pdf';
    }

    public function getDocumentFileUrlAttribute(): ?string
    {
        return $this->getCompressedDocumentUrlAttribute();
    }

    public function getProofAddressUrlAttribute(): ?string
    {
        return $this->getCompressedAddressUrlAttribute();
    }

    public function hasDocumentFile(): bool
    {
        return $this->hasMedia('verification_documents');
    }

    public function hasProofAddress(): bool
    {
        return $this->hasMedia('verification_address');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /** ---------------------------
     *  Helpers
     *  --------------------------*/
    protected function isImage(Media $media): bool
    {
        return str_starts_with($media->mime_type, 'image/');
    }
}
