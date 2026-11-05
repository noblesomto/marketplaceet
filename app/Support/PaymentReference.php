<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Flutterwave references start with this site's country code (ET-MKT-…)
 * so the shared webhook router can tell which marketplace a charge or
 * transfer belongs to when several countries use one Flutterwave account.
 */
class PaymentReference
{
    /** Used when FLUTTERWAVE_SITE is missing or not a two-letter country code. */
    public const DEFAULT_SITE = 'ET';

    public static function make(string $kind, int $length = 20): string
    {
        return self::prefix() . $kind . '-' . Str::upper(Str::random($length));
    }

    /** Checkout tx_ref for Buy Direct orders and boosts. */
    public static function charge(): string
    {
        return self::make('MKT');
    }

    /** Transfer reference for a seller payout attempt. */
    public static function payout(int $paymentId, int $attempt): string
    {
        return self::make("MKT-PAYOUT-{$paymentId}-{$attempt}", 6);
    }

    /** Boost requested from the mobile app, before any checkout. */
    public static function boostRequest(): string
    {
        return self::make('BOOST', 16);
    }

    public static function site(): string
    {
        $site = strtoupper(trim((string) config('services.flutterwave.site')));

        return preg_match('/^[A-Z]{2}$/', $site) ? $site : self::DEFAULT_SITE;
    }

    public static function prefix(): string
    {
        return self::site() . '-';
    }

    /**
     * True when the reference carries another country's prefix (KE-MKT-…).
     * Unprefixed references (MKT-…, created before prefixes) are ours.
     */
    public static function isForAnotherSite(?string $reference): bool
    {
        return (bool) preg_match('/^([A-Z]{2})-/', (string) $reference, $m) && $m[1] !== self::site();
    }
}
