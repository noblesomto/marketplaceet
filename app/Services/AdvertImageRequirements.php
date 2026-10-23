<?php

namespace App\Services;

use App\Models\AdSetting;

/**
 * Single source of truth for advert image requirements (count, dimensions, file size, formats).
 *
 * Admins configure these via /settings/ad-images (AdSetting key/value store, cached 1hr per key).
 * Server-side validation (AdvertValidationService, ImageQualityService) and the API's
 * GET /api/adverts/image-requirements endpoint both read from here, so the rules enforced
 * server-side and the rules handed to the mobile/web clients for local pre-validation can
 * never drift apart.
 */
class AdvertImageRequirements
{
    const ALLOWED_MIMES = ['jpeg', 'png', 'jpg', 'gif'];

    public static function get(): array
    {
        $minFileSizeKb = (int) AdSetting::getValue('image_min_file_size_kb', 50);
        $maxFileSizeMb = (int) AdSetting::getValue('image_max_file_size_mb', 20);

        return [
            'min_images'          => (int) AdSetting::getValue('min_images', 3),
            'max_images'          => (int) AdSetting::getValue('max_images', 8),
            'min_width'           => (int) AdSetting::getValue('image_min_width', 800),
            'min_height'          => (int) AdSetting::getValue('image_min_height', 600),
            'recommended_width'   => (int) AdSetting::getValue('image_recommended_width', 1200),
            'recommended_height'  => (int) AdSetting::getValue('image_recommended_height', 900),
            'min_file_size_bytes' => $minFileSizeKb * 1024,
            'max_file_size_bytes' => $maxFileSizeMb * 1024 * 1024,
            'max_file_size_kb'    => $maxFileSizeMb * 1024, // Laravel's `max:` file rule is in kilobytes
            'allowed_formats'     => self::ALLOWED_MIMES,
            'strictness'          => (int) AdSetting::getValue('image_strictness', 7),
        ];
    }
}
