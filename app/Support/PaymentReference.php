<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Flutterwave references start with this site's country code (GH-MKT-…)
 * so the shared webhook router can tell which marketplace a charge or
 * transfer belongs to when several countries use one Flutterwave account.
 */
class PaymentReference
{
    public static function make(string $kind, int $length = 20): string
    {
        return self::prefix() . $kind . '-' . Str::upper(Str::random($length));
    }

    public static function prefix(): string
    {
        $site = strtoupper(trim((string) config('services.flutterwave.site', 'ET')));

        return $site === '' ? '' : $site . '-';
    }
}
