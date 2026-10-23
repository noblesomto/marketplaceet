<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Computes the suffix EloquentSluggable appends when a slug collides.
 *
 * Given a parent slug (e.g. the owning subcategory's slug), prefers a
 * descriptive suffix ("apple-tablets") over the package's default numeric
 * counter ("apple-2") — falling back to a numeric suffix only when the
 * parent-qualified slug is itself already taken (a genuine duplicate within
 * the same parent), or when there's no parent slug to qualify with.
 */
class TaxonomySlugDisambiguator
{
    public static function suffix(string $slug, string $separator, Collection $existingSlugs, $firstSuffix, ?string $parentSlug): string
    {
        if ($parentSlug === null || $parentSlug === '') {
            return self::numericSuffix($slug, $separator, $existingSlugs, $firstSuffix);
        }

        $candidate = $slug . $separator . $parentSlug;

        if (!$existingSlugs->contains($candidate)) {
            return $parentSlug;
        }

        $n = $firstSuffix;
        while ($existingSlugs->contains($candidate . $separator . $n)) {
            $n++;
        }

        return $parentSlug . $separator . $n;
    }

    private static function numericSuffix(string $slug, string $separator, Collection $existingSlugs, $firstSuffix): string
    {
        $prefix = $slug . $separator;
        $len = strlen($prefix);

        $max = $existingSlugs
            ->filter(fn ($value) => is_string($value) && str_starts_with($value, $prefix))
            ->map(fn ($value) => (int) substr($value, $len))
            ->push(0)
            ->max();

        return (string) ($max === 0 ? $firstSuffix : $max + 1);
    }
}
