<?php

namespace App\Services;

use App\Models\AdSetting;

/**
 * Admin-managed Buy Direct (escrow) settings, stored in the AdSetting
 * key/value table and edited at /settings/buy-direct.
 */
class BuyDirectSettings
{
    public const DEFAULTS = [
        'buy_direct_ship_deadline_days'    => 3,
        'buy_direct_auto_release_days'     => 7,
        'buy_direct_commission_rate'       => 3,      // % for items below the threshold
        'buy_direct_commission_rate_high'  => 2,      // % for items at/above the threshold
        'buy_direct_commission_threshold'  => 300000, // Br
        'buy_direct_auto_payout'           => 1,
        'buy_direct_auto_refund'           => 1,
    ];

    public static function all(): array
    {
        $out = [];
        foreach (self::DEFAULTS as $key => $default) {
            $out[$key] = AdSetting::getValue($key, $default);
        }

        return $out;
    }

    public static function set(array $values): void
    {
        foreach (array_keys(self::DEFAULTS) as $key) {
            if (array_key_exists($key, $values)) {
                AdSetting::setValue($key, $values[$key]);
            }
        }
    }

    public static function shipDeadlineDays(): int
    {
        return max(1, (int) AdSetting::getValue('buy_direct_ship_deadline_days', self::DEFAULTS['buy_direct_ship_deadline_days']));
    }

    public static function autoReleaseDays(): int
    {
        return max(1, (int) AdSetting::getValue('buy_direct_auto_release_days', self::DEFAULTS['buy_direct_auto_release_days']));
    }

    public static function autoPayout(): bool
    {
        return (bool) (int) AdSetting::getValue('buy_direct_auto_payout', self::DEFAULTS['buy_direct_auto_payout']);
    }

    public static function autoRefund(): bool
    {
        return (bool) (int) AdSetting::getValue('buy_direct_auto_refund', self::DEFAULTS['buy_direct_auto_refund']);
    }

    /** Commission percentage that applies to an item at this price. */
    public static function commissionRateFor(float $price): float
    {
        $threshold = (float) AdSetting::getValue('buy_direct_commission_threshold', self::DEFAULTS['buy_direct_commission_threshold']);

        $key = $price >= $threshold ? 'buy_direct_commission_rate_high' : 'buy_direct_commission_rate';

        return (float) AdSetting::getValue($key, self::DEFAULTS[$key]);
    }

    public static function commissionFor(float $price): float
    {
        return round($price * self::commissionRateFor($price) / 100, 2);
    }

    /**
     * Server-side price breakdown for an item. Clients never supply amounts —
     * always recompute from the advert price.
     */
    public static function quote(float $price): array
    {
        $commission = self::commissionFor($price);

        return [
            'item_price'      => round($price, 2),
            'commission_rate' => self::commissionRateFor($price),
            'commission'      => $commission,
            'shipping_cost'   => 0,
            'grand_total'     => round($price + $commission, 2),
        ];
    }
}
