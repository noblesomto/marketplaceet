<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SisterSite extends Model
{
    use HasFactory;

    const CACHE_KEY = 'sister_sites_active';

    protected $fillable = [
        'country_name',
        'flag',
        'url',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Scope a query to only include active sister sites.
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
     * Active sister sites, ordered, cached for public display.
     */
    public static function activeOrdered()
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return self::active()->ordered()->get();
        });
    }

    /**
     * Flush the cached active list. Call after any admin create/update/delete/toggle.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted()
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }
}
