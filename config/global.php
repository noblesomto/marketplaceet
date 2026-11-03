<?php


return [
    'site_name' => 'Marketplace Ethiopia',
    'site_title' => 'Buy & Sell on Marketplace Ethiopia',
    'email_title' => 'Marketplace Ethiopia',
    'admin_email' => 'adminstrator@marketplace.com.et',
    'dispute_email' => 'disputes@marketplace.com.et',
    // TODO: placeholder — replace with the real business phone/address for Ethiopia.
    // Courier integrations (shipping companies) for Buy Direct. Off for Ethiopia:
    // sellers arrange delivery with the buyer directly.
    'shipping_carriers_enabled' => env('SHIPPING_CARRIERS_ENABLED', false),

    'site_phone' => '+251 91 234 5678',
    'site_email' => 'adminstrator@marketplace.com.et',
    'site_address' => 'Bole Road, Addis Ababa, Ethiopia',

    // Mobile app store listings. Leave unset until the Ethiopia apps are
    // published — every install banner and store badge hides when empty.
    'ios_app_id' => env('IOS_APP_ID'),
    'ios_app_url' => env('IOS_APP_URL'),
    'android_app_url' => env('ANDROID_APP_URL'),

    // Official social accounts (footer + Organization JSON-LD sameAs).
    // Empty entries are not rendered.
    'social_links' => array_filter([
        'Facebook' => env('SOCIAL_FACEBOOK_URL'),
        'Instagram' => env('SOCIAL_INSTAGRAM_URL'),
        'X' => env('SOCIAL_X_URL'),
        'TikTok' => env('SOCIAL_TIKTOK_URL'),
        'YouTube' => env('SOCIAL_YOUTUBE_URL'),
        'Pinterest' => env('SOCIAL_PINTEREST_URL'),
        'Whatsapp' => env('SOCIAL_WHATSAPP_CHANNEL_URL'),
    ]),

];
