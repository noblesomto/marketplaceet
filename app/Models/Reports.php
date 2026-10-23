<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reports extends Model
{
    use HasFactory;
    protected $fillable = [
        'advert_id',
        'user_id',
        'subject',
        'message',
        'reported_ad_title',
        'reported_ad_number',
        'reported_seller_id',
        'reported_seller_name',
        'reported_seller_phone',
        'reported_seller_email',
    ];

    public function adverts()
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The seller/ad-owner's user account, resolved via the id snapshotted
     * at report time. Kept even if the advert itself is deleted, so the
     * seller can still be identified (e.g. for a law-enforcement request)
     * as long as their account still exists.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'reported_seller_id', 'user_id');
    }

    /**
     * Whether the reported advert still exists. Adverts are hard-deleted
     * (no SoftDeletes), so a report can outlive the ad it was filed against.
     */
    public function getAdIsLiveAttribute(): bool
    {
        return $this->adverts !== null;
    }

    /**
     * Ad title to display: live title when the advert still exists (so
     * admins see the current title if it was edited after the report),
     * otherwise the title snapshotted at report time, otherwise unknown.
     */
    public function getDisplayAdTitleAttribute(): string
    {
        return $this->adverts->ad_title
            ?? $this->reported_ad_title
            ?? 'Ad deleted';
    }

    /**
     * Public-facing ad number to display, same live-first-then-snapshot
     * preference as displayAdTitle.
     */
    public function getDisplayAdNumberAttribute(): ?string
    {
        return $this->adverts->ad_id
            ?? $this->reported_ad_number
            ?? null;
    }

    /**
     * Seller identity to display: prefers the live account (current
     * advert owner if the ad still exists, else the account found via the
     * snapshotted seller id) so admins see up-to-date contact details;
     * falls back to the name/phone/email snapshotted at report time if the
     * seller's account is also gone. This is what makes a deleted ad's
     * report still usable for a law-enforcement handoff.
     */
    protected function resolveSellerField(string $liveField, ?string $snapshotColumn = null)
    {
        if ($this->adverts && $this->adverts->owner) {
            return $this->adverts->owner->{$liveField};
        }

        if ($this->reported_seller_id && $this->seller) {
            return $this->seller->{$liveField};
        }

        return $snapshotColumn ? $this->{$snapshotColumn} : null;
    }

    public function getDisplaySellerIdAttribute(): ?string
    {
        return $this->adverts->owner->user_id
            ?? $this->reported_seller_id
            ?? null;
    }

    public function getDisplaySellerNameAttribute(): string
    {
        return $this->resolveSellerField('name', 'reported_seller_name') ?? 'Unknown';
    }

    public function getDisplaySellerPhoneAttribute(): ?string
    {
        return $this->resolveSellerField('phone', 'reported_seller_phone');
    }

    public function getDisplaySellerEmailAttribute(): ?string
    {
        return $this->resolveSellerField('email', 'reported_seller_email');
    }
}
