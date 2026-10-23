<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SlugRedirectResolver
{
    public function resolve(string $type, string $oldSlug): ?string
    {
        return DB::table('slug_redirects')
            ->where('sluggable_type', $type)
            ->where('old_slug', $oldSlug)
            ->value('new_slug');
    }

    public function record(string $type, string $oldSlug, string $newSlug): void
    {
        // Decide insert-vs-update from an explicit exists() check, not from
        // update()'s affected-row count: MySQL reports rows *changed*, not
        // rows *matched* (PDO::MYSQL_ATTR_FOUND_ROWS isn't set), so
        // re-recording an identical triple within the same second would
        // update 0 rows and incorrectly fall through to insert(), violating
        // the unique(['sluggable_type', 'old_slug']) constraint.
        $exists = DB::table('slug_redirects')
            ->where('sluggable_type', $type)
            ->where('old_slug', $oldSlug)
            ->exists();

        if ($exists) {
            DB::table('slug_redirects')
                ->where('sluggable_type', $type)
                ->where('old_slug', $oldSlug)
                ->update(['new_slug' => $newSlug, 'updated_at' => now()]);
        } else {
            DB::table('slug_redirects')->insert([
                'sluggable_type' => $type,
                'old_slug' => $oldSlug,
                'new_slug' => $newSlug,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
