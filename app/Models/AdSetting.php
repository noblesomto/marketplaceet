<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key, with cache and a fallback default.
     */
    public static function getValue(string $key, $default = null): mixed
    {
        return Cache::remember("ad_setting_{$key}", 3600, function () use ($key, $default) {
            $row = static::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    /**
     * Persist a setting and clear its cache entry.
     */
    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("ad_setting_{$key}");
    }
}
