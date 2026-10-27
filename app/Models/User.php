<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Notification as AppNotification;


class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_id',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'password',
        'verified',
        'acc_status',
        'acc_type',
        'source',
        'token',
        'otp',
        'profile_picture',
        'notification',
        'disable_account',
        'disable_account_date',
        'disabled_by',
        'disable_reason',
        'bank_name',
        'bank_code',
        'account_name',
        'account_number',
        'remember_token',
        'otp_expires_at',
        'push_notifications_enabled',
        'google_id',
        'facebook_id',
        'apple_id',
        'avatar',
        'last_login_ip',
        'last_login_at',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'otp_expires_at',
        'token',
        'last_login_ip',
        'google_id',
        'facebook_id',
        'apple_id',
    ];

    /**
     * Account details only the user themselves (or an admin) may see. Users
     * appear in other people's responses — an ad's seller, a message's sender
     * — and there they get the public profile without these.
     *
     * @var array<int, string>
     */
    public const PRIVATE_FIELDS = [
        'email',
        'address',
        'bank_name',
        'bank_code',
        'account_name',
        'account_number',
        'notification',
        'push_notifications_enabled',
        'notifications_seen_at',
        'disable_account_date',
        'disabled_by',
        'disable_reason',
    ];

    public function toArray()
    {
        $data = parent::toArray();

        return $this->viewerCanSeePrivateFields() ? $data : array_diff_key($data, array_flip(self::PRIVATE_FIELDS));
    }

    private function viewerCanSeePrivateFields(): bool
    {
        if (auth('admin')->check()) {
            return true;
        }

        // The sanctum guard covers API tokens and the web session.
        return auth('sanctum')->id() === $this->getKey();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'push_notifications_enabled' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            do {
                $user->user_id = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            } while (User::where('user_id', $user->user_id)->exists());
        });
    }

     protected $appends = [
        'profile_image_url',
        'profile_thumbnail_url'
    ];

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function trustedDevices()
    {
        return $this->hasMany(TrustedDevice::class, 'user_id', 'user_id');
    }


    public function adverts()
    {
        return $this->hasMany(Advert::class, 'user_id', 'user_id');
    }

    public function verification()
    {
        return $this->hasOne(UserVerification::class, 'user_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', 'user_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id', 'user_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'user_id', 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id', 'user_id');
    }

    public function feedbacksGiven()
    {
        return $this->hasMany(Feedback::class, 'user_id', 'user_id');
    }

    public function feedbacksReceived()
    {
        return $this->hasMany(Feedback::class, 'seller_id', 'user_id');
    }

    public function followers()
    {
        return $this->hasMany(Followers::class, 'follow', 'user_id');
    }

    public function following()
    {
        return $this->hasMany(Followers::class, 'user_id', 'user_id');
    }

    public function blockedUsers()
    {
        return $this->hasMany(BlockedUser::class, 'blocker_id', 'user_id');
    }

    public function blockedBy()
    {
        return $this->hasMany(BlockedUser::class, 'blocked_id', 'user_id');
    }

    // Helper methods
    public function hasBlocked($userId, $advertId = null)
    {
        $query = $this->blockedUsers()
            ->where('blocked_id', $userId);

        if ($advertId) {
            $query->where(function($q) use ($advertId) {
                $q->where('advert_id', $advertId)
                  ->orWhereNull('advert_id'); // global blocks
            });
        }

        return $query->exists();
    }

    public function isBlockedBy($userId, $advertId = null)
    {
        $query = $this->blockedBy()
            ->where('blocker_id', $userId);

        if ($advertId) {
            $query->where(function($q) use ($advertId) {
                $q->where('advert_id', $advertId)
                  ->orWhereNull('advert_id');
            });
        }

        return $query->exists();
    }


    public function archivedMessages()
    {
        return $this->hasMany(ArchivedMessage::class, 'user_id', 'user_id');
    }

    public function hasArchivedConversation($advertId, $otherUserId)
    {
        return $this->archivedMessages()
            ->where('advert_id', $advertId)
            ->where('other_user_id', $otherUserId)
            ->exists();
    }

    public function getArchivedConversation($advertId, $otherUserId)
    {
        return $this->archivedMessages()
            ->where('advert_id', $advertId)
            ->where('other_user_id', $otherUserId)
            ->first();
    }



    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_image')
            ->singleFile()
            ->onlyKeepLatest(1)
            ->acceptsMimeTypes(['image/jpeg','image/png','image/webp','image/gif'])
            ->useDisk('spatie')
            ->useFallbackUrl('/images/placeholder-profile.png');
    }



    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(70)
            ->width(1200)
            ->fit(Fit::Max)
            ->optimize()
            ->performOnCollections('profile_image')
            ->nonQueued();

        $this->addMediaConversion('thumbnail')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->quality(50)
            ->fit(Fit::Crop)
            ->optimize()
            ->performOnCollections('profile_image')
            ->nonQueued();
    }

    // ADD THIS METHOD TO YOUR USER MODEL:
    public function afterMediaConversion(Media $media): void
    {
        // Wait 2 seconds then delete original file
        sleep(2);

        if ($media->collection_name === 'profile_image') {
            $disk = $media->getDisk();
            $originalPath = $media->getPath();

            if ($disk->exists($originalPath)) {
                $disk->delete($originalPath);
            }
        }
    }


    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('profile_image', 'optimized');
    }

    public function getProfileThumbnailUrlAttribute(): string
    {
        if ($this->hasMedia('profile_image')) {
            return $this->getFirstMediaUrl('profile_image', 'thumbnail');
        }

        $initials = \App\Helpers\AvatarHelper::generateInitials($this->name);
        $bgColor = \App\Helpers\AvatarHelper::generateColor($this->name);

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">
            <rect width="128" height="128" fill="{$bgColor}" rx="64"/>
            <text x="50%" y="50%" text-anchor="middle" dy="0.35em" font-family="Arial, sans-serif" font-size="48" font-weight="600" fill="white">{$initials}</text>
        </svg>
        SVG;

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

   /**
     * Get all active device tokens for this user
     *
     * IMPORTANT: device_tokens.user_id references users.id (not users.user_id)
     */
    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class, 'user_id', 'id')
            ->where('is_active', true);
    }

    /**
     * Check if user can receive push notifications
     */
    public function canReceivePushNotifications(): bool
    {
        return $this->push_notifications_enabled && $this->deviceTokens()->exists();
    }

    /**
     * Count unread notifications — used for iOS badge count.
     * Mirrors the logic in getUserNotificationCount() helper.
     */
    public function unreadNotificationCount(): int
    {
        $query = AppNotification::where('user_id', $this->id);

        if ($this->notifications_seen_at) {
            $query->where('created_at', '>', $this->notifications_seen_at);
        } else {
            $query->where('is_read', false);
        }

        return $query->count();
    }


}
