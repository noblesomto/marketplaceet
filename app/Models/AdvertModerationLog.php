<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertModerationLog extends Model
{
    public const REASON_CATEGORIES = [
        'fraudulent'  => 'Fraudulent or scam listing',
        'misleading'  => 'Misleading or inaccurate information',
        'poor_images' => 'Poor quality / inappropriate images',
        'prohibited'  => 'Prohibited or illegal item',
        'duplicate'   => 'Duplicate listing',
        'policy'      => 'Violates marketplace policy',
        'other'       => 'Other',
    ];

    protected $fillable = [
        'advert_id',
        'admin_id',
        'action',
        'reason_category',
        'reason_note',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function getReasonCategoryLabelAttribute(): ?string
    {
        return self::REASON_CATEGORIES[$this->reason_category] ?? $this->reason_category;
    }
}
