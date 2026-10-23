<?php

namespace App\Support;

use App\Models\Advert;

class AdvertVisibility
{
    /**
     * Manually flagged spam/fraud ad_ids that should never be servable, even
     * though the row is still in the database. Kept here instead of the DB
     * so both the web and API controllers check the same list.
     */
    protected static array $blockedAdIds = [47428, 86031, 34955, 86795, 44956];

    /**
     * Why (if at all) an advert should not be shown to visitors. Returns null
     * when the advert is visible. Shared by the web detail page and the API
     * so both surfaces make the same "is this ad gone" decision from one
     * place instead of drifting apart.
     */
    public static function reasonUnavailable(?Advert $ad): ?string
    {
        if (!$ad) {
            return 'not_found';
        }

        if (in_array((int) $ad->ad_id, self::$blockedAdIds, true)) {
            return 'blocked';
        }

        if ($ad->redirect === 'Yes') {
            return 'removed_by_admin';
        }

        return null;
    }
}
