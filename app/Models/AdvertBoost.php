<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AdvertBoost extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'advert_id',
        'user_id',
        'payment_reference',
        'trans_id',
        'amount',
        'duration',
        'payment_status',
        'boost_status',
        'boost_type',
        'start_date',
        'upload_proof',
        'boost_type_id',
        'duration_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
    ];

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('payment_proof')
            ->singleFile() // Only one proof per boost
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp', 'application/pdf']);
    }

    /**
     * Get the payment proof URL
     */
    public function getPaymentProofUrlAttribute()
    {
        $media = $this->getFirstMedia('payment_proof');
        return $media ? $media->getUrl() : null;
    }

    /**
     * Check if payment proof exists
     */
    public function hasPaymentProof(): bool
    {
        return $this->hasMedia('payment_proof');
    }

    /**
     * Claim a short-lived slot for starting a boost payment so double-clicks
     * and client retries don't create a burst of identical pending rows.
     * Returns false if the same user/advert started one moments ago.
     */
    public static function claimInitSlot($userId, $advertId, int $seconds = 20): bool
    {
        return Cache::add("boost-init:{$userId}:{$advertId}", 1, $seconds);
    }

    public function advert()
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function boostType()
    {
        return $this->belongsTo(BoostType::class, 'boost_type_id');
    }

    public function boostDuration()
    {
        return $this->belongsTo(BoostDuration::class, 'duration_id');
    }

    public function getDaysRemainingAttribute()
    {
        $expiry = Carbon::parse($this->start_date)->addDays($this->duration);
        return Carbon::now()->diffInDays($expiry, false); // negative means expired
    }

    public function getExpiresAtAttribute()
    {
        return $this->start_date->copy()->addDays($this->duration);
    }

    public function getIsActiveAttribute()
    {
        return $this->boost_status === 'active' && $this->expires_at->isFuture();
    }
}
