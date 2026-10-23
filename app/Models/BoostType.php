<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoostType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'daily_rate',
        'display_order',
        'is_active',
        'description',
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Scope a query to only include active boost types.
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
     * Get all advert boosts of this type.
     */
    public function advertBoosts()
    {
        return $this->hasMany(AdvertBoost::class, 'boost_type_id');
    }

    /**
     * Calculate price for a given duration.
     */
    public function calculatePrice($days, $discountPercentage = 0)
    {
        $basePrice = $this->daily_rate * $days;
        $discount = ($basePrice * $discountPercentage) / 100;
        return $basePrice - $discount;
    }
}
