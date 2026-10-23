<?php

namespace App\Support;

class ShippingStatusBadge
{
    /**
     * Resolve the single order-level status badge shown to both seller and buyer.
     * Priority: buyer_status (once delivered) > shipping_status (once the shipper
     * has taken over) > seller_status (before the shipper has touched it).
     *
     * `class` is Tailwind, for the web views. `color` is a plain semantic name
     * (green/orange/blue/purple/red/amber) for API consumers (mobile) that
     * don't use Tailwind — pick your own hex per name on that end.
     */
    public static function resolve(string $sellerStatus, string $shipStatus, string $buyerStatus, string $shipCompany): array
    {
        if ($buyerStatus === 'delivered') {
            return ['stage' => 'delivered', 'label' => 'Delivered', 'icon' => 'bi-check-circle-fill', 'color' => 'green', 'class' => 'bg-green-100 text-green-700 border-green-200'];
        }

        if ($shipStatus !== 'pending') {
            return match ($shipStatus) {
                'delivered' => ['stage' => 'ship_delivered', 'label' => 'Pending Confirmation', 'icon' => 'bi-hourglass-split', 'color' => 'orange', 'class' => 'bg-orange-100 text-orange-700 border-orange-200'],
                'shipped'   => ['stage' => 'ship_shipped', 'label' => 'Shipped', 'icon' => 'bi-truck', 'color' => 'blue', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
                'pickup'    => ['stage' => 'ship_pickup', 'label' => 'Ready for Pickup', 'icon' => 'bi-shop', 'color' => 'purple', 'class' => 'bg-purple-100 text-purple-700 border-purple-200'],
                'canceled'  => ['stage' => 'canceled', 'label' => 'Canceled', 'icon' => 'bi-x-circle-fill', 'color' => 'red', 'class' => 'bg-red-100 text-red-700 border-red-200'],
                default     => ['stage' => 'pending', 'label' => 'Pending', 'icon' => 'bi-clock', 'color' => 'amber', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
            };
        }

        if ($sellerStatus === 'shipped') {
            return ['stage' => 'seller_shipped', 'label' => "Dropped off at {$shipCompany}", 'icon' => 'bi-box-seam-fill', 'color' => 'blue', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'];
        }

        if ($sellerStatus === 'canceled') {
            return ['stage' => 'canceled', 'label' => 'Canceled', 'icon' => 'bi-x-circle-fill', 'color' => 'red', 'class' => 'bg-red-100 text-red-700 border-red-200'];
        }

        return ['stage' => 'pending', 'label' => 'Pending', 'icon' => 'bi-clock', 'color' => 'amber', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'];
    }

    /**
     * Resolve the courier's own badge, shown next to the shipping company
     * (e.g. in the "Selected Shipping" card). Reflects shipping_status only —
     * not seller_status or buyer_status.
     */
    public static function resolveShippingOnly(string $shipStatus): array
    {
        return match ($shipStatus) {
            'delivered' => ['label' => 'Delivered', 'icon' => 'bi-check-circle-fill', 'color' => 'green', 'class' => 'bg-green-100 text-green-700 border-green-200'],
            'shipped'   => ['label' => 'Shipped', 'icon' => 'bi-truck', 'color' => 'blue', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
            'pickup'    => ['label' => 'Ready for Pickup', 'icon' => 'bi-shop', 'color' => 'purple', 'class' => 'bg-purple-100 text-purple-700 border-purple-200'],
            'canceled'  => ['label' => 'Canceled', 'icon' => 'bi-x-circle-fill', 'color' => 'red', 'class' => 'bg-red-100 text-red-700 border-red-200'],
            default     => ['label' => 'Pending', 'icon' => 'bi-clock', 'color' => 'amber', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
        };
    }

    /**
     * The seller-facing message shown in place of the "Update Shipping
     * Status" form once the shipper has taken over (shipping_status is no
     * longer 'pending'). Distinguishes delivered/canceled from still-in-
     * transit, so a completed order doesn't read like it's stuck awaiting
     * support. Returns null for 'pending' — the form is shown instead.
     *
     * Used by: web (resources/views/user/ad-shipping.blade.php) and API
     * (Api\UserController@adShippingDetails, 'locked_message' field).
     */
    public static function sellerLockedMessage(string $shipStatus, ?string $shipCompany): ?string
    {
        $shipCompany = $shipCompany ?: 'the shipping company';

        return match ($shipStatus) {
            'shipped'   => "This order is already with {$shipCompany} and on its way to the buyer, so it can no longer be updated from here. Contact support if you need to make a change.",
            'pickup'    => "This order is ready for pickup at {$shipCompany}, so it can no longer be updated from here. Contact support if you need to make a change.",
            'delivered' => "This order has been delivered to the buyer. No further action is needed here.",
            'canceled'  => "This order has been canceled. Contact support if you need assistance.",
            default     => null,
        };
    }

    /**
     * The buyer-facing status banner shown just under the page title on the
     * order-details page (web: resources/views/user/order-details.blade.php,
     * API: Api\UserController@orderDetails). Returns null for the 'pending'
     * stage (no banner shown). $stage is $badge['stage'] from resolve().
     *
     * Single source of truth for this copy — both the web view and the API
     * call this instead of each keeping their own copy of the text, so they
     * can't drift apart.
     */
    public static function bannerFor(string $stage, ?string $shipCompany, $shippingStatusDate, $sellerStatusDate, ?string $pickupAddress = null): ?array
    {
        $shipCompany = $shipCompany ?: 'the shipping company';
        $shipDate    = $shippingStatusDate ? date('d M Y', strtotime($shippingStatusDate)) : null;
        $sellerDate  = $sellerStatusDate ? date('d M Y', strtotime($sellerStatusDate)) : null;

        return match ($stage) {
            'delivered' => [
                'icon'  => 'bi-check-circle-fill',
                'color' => 'green',
                'class' => 'bg-green-50 border border-green-200 text-green-800',
                'text'  => 'Order delivered' . ($shipDate ? " on {$shipDate}" : '') . '. Thank you for shopping with us!',
            ],
            'ship_delivered' => [
                'icon'  => 'bi-hourglass-split',
                'color' => 'orange',
                'class' => 'bg-orange-50 border border-orange-200 text-orange-800',
                'text'  => 'Your order has been marked as delivered' . ($shipDate ? " on {$shipDate}" : '') . '. Please confirm receipt below.',
            ],
            'ship_shipped' => [
                'icon'  => 'bi-truck',
                'color' => 'blue',
                'class' => 'bg-blue-50 border border-blue-200 text-blue-800',
                'text'  => 'Your order has been shipped' . ($shipDate ? " on {$shipDate}" : '') . '. Estimated delivery: 3–7 working days.',
            ],
            'ship_pickup' => [
                'icon'  => 'bi-shop',
                'color' => 'purple',
                'class' => 'bg-purple-50 border border-purple-200 text-purple-800',
                'text'  => "Your order is ready for pickup at {$shipCompany}"
                            . ($pickupAddress ? ", {$pickupAddress}" : '')
                            . ($shipDate ? " (updated {$shipDate})" : '')
                            . '.',
            ],
            'seller_shipped' => [
                'icon'  => 'bi-box-seam-fill',
                'color' => 'blue',
                'class' => 'bg-blue-50 border border-blue-200 text-blue-800',
                'text'  => "The seller has dropped off your order at {$shipCompany}" . ($sellerDate ? " on {$sellerDate}" : '') . '. It will be on its way to you shortly.',
            ],
            'canceled' => [
                'icon'  => 'bi-x-circle-fill',
                'color' => 'red',
                'class' => 'bg-red-50 border border-red-200 text-red-800',
                'text'  => 'This order has been canceled. A refund is being processed and will be credited back to you shortly. Please contact support if you need help.',
            ],
            default => null,
        };
    }
}
