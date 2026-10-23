<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoostDuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'days',
        'discount_percentage',
        'label',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'days' => 'integer',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Scope a query to only include active durations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by display_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    /**
     * Get all advert boosts with this duration.
     */
    public function advertBoosts()
    {
        return $this->hasMany(AdvertBoost::class, 'duration_id');
    }
}
