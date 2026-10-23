<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'flutterwave' => [
        'publicKey' => env('FLUTTERWAVE_PUBLIC_KEY'),
        'secretKey' => env('FLUTTERWAVE_SECRET_KEY'),
        'encryptionKey' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
        'webhookHash' => env('FLUTTERWAVE_WEBHOOK_HASH'),
        'paymentUrl' => env('FLUTTERWAVE_PAYMENT_URL', 'https://api.flutterwave.com/v3'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URL'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URL'),
    ],

    'apple' => [
        // Bundle ID for iOS, or Service ID for web — must match the `aud` claim in Apple's identity token
        'client_id' => env('APPLE_CLIENT_ID'),
    ],
    'recaptcha' => [
        'enabled' => env('RECAPTCHA_ENABLED', false), // Set to false to disable temporarily
        'site_key' => env('GOOGLE_RECAPTCHA_KEY'),
        'secret_key' => env('GOOGLE_RECAPTCHA_SECRET'),
    ],
    'fcm' => [
        'credentials' => storage_path('app/firebase/firebase-credentials.json'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
    ],


];
