<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Thin wrapper around Spatie's activity() helper.
 *
 * User's Eloquent primary key is overridden to the display-facing
 * 'user_id' string (App\Models\User::$primaryKey), not the surrogate
 * bigint 'id' column that the activity_log table's polymorphic
 * subject_id/causer_id columns (unsignedBigInteger) expect — the same
 * landmine already documented for notifications.user_id elsewhere in
 * this app. Passing a User model straight into causedBy()/performedOn()
 * would silently truncate the stored id. Always log through record()
 * rather than calling activity() directly when a User is involved.
 */
class ActivityLog
{
    public static function record(
        string $logName,
        string $description,
        ?Model $causer = null,
        ?Model $subject = null,
        array $properties = []
    ): void {
        $log = activity($logName)->withProperties($properties);

        if ($causer) {
            $log->causedBy(self::normalize($causer));
        } else {
            $log->causedByAnonymous();
        }

        if ($subject) {
            $log->performedOn(self::normalize($subject));
        }

        $log->log($description);
    }

    // Resolve the real bigint id for a User causer/subject so it matches
    // what the morph columns can actually store. See class docblock.
    public static function normalize(Model $model): Model
    {
        if ($model instanceof User) {
            $keyed = clone $model;

            // User::$incrementing is false (its declared key is user_id), so
            // Eloquent skips populating ->id after a fresh ::create() even
            // though MySQL did assign one — refresh to pick it up rather
            // than silently logging a null causer/subject.
            if ($keyed->id === null && $keyed->exists) {
                $keyed->refresh();
            }

            $keyed->setKeyName('id');

            return $keyed;
        }

        return $model;
    }
}
