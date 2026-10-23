<?php

namespace App\Services;

use App\Jobs\SendShippingUpdatePushNotification;
use App\Mail\CancelAdMail;
use App\Mail\DeliverAdMail;
use App\Mail\PickupAdMail;
use App\Mail\SellerDeliverAdMail;
use App\Mail\ShipAdMail;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the buyer/seller email + in-app + push notifications for a
 * shipping_status change. Shared by the shipper portal and the admin
 * payment editor so a status change always produces the same
 * customer-facing notifications, regardless of who made it.
 */
class ShippingStatusNotifier
{
    public static function notify(Payment $payment, string $status): void
    {
        $payment->loadMissing(['advert.user', 'user', 'shipping', 'cityLocation.state']);

        $buyer   = $payment->user;
        $seller  = $payment->advert->user ?? null;
        $company = $payment->shipping->company ?? 'the shipping company';
        $adTitle = $payment->advert->ad_title ?? 'the order';
        $city    = $payment->cityLocation;

        $details = [
            'advert'       => $adTitle,
            'seller'       => $seller->name ?? '—',
            'buyer'        => $buyer->name ?? trim(($payment->first_name ?? '') . ' ' . ($payment->last_name ?? '')),
            'phone'        => $payment->phone,
            'shipping'     => $company,
            'tracking_id'  => $payment->tracking_id,
            'shipped_date' => now(),
            'address'      => $city->address ?? '—',
            'city'         => $city->city ?? '—',
            'state'        => $city->state->name ?? '—',
        ];

        if ($buyer && $buyer->email) {
            try {
                match ($status) {
                    'shipped'   => Mail::to($buyer->email)->send(new ShipAdMail($details)),
                    'pickup'    => Mail::to($buyer->email)->send(new PickupAdMail($details)),
                    'delivered' => Mail::to($buyer->email)->send(new DeliverAdMail($details)),
                    'canceled'  => Mail::to($buyer->email)->send(new CancelAdMail($details)),
                    default     => null,
                };
            } catch (\Exception $e) {
                Log::error('Shipping-status buyer email failed: ' . $e->getMessage());
            }
        }

        if ($status === 'delivered' && $seller && $seller->email) {
            try {
                Mail::to($seller->email)->send(new SellerDeliverAdMail($details));
            } catch (\Exception $e) {
                Log::error('Shipping-status seller delivery email failed: ' . $e->getMessage());
            }
        }

        if ($buyer) {
            $buyerMessages = [
                'shipped'   => "Your order \"{$adTitle}\" has been shipped by {$company} and is on its way.",
                'pickup'    => "Your order \"{$adTitle}\" is ready for pickup at your nearest {$company} centre.",
                'delivered' => "Your order \"{$adTitle}\" has been delivered by {$company}. Please confirm receipt.",
                'canceled'  => "{$company} has an update on the shipment of \"{$adTitle}\". Please check your order details.",
            ];

            try {
                Notification::create([
                    'user_id'   => $buyer->id,
                    'seller_id' => $seller->id ?? $buyer->id,
                    'advert_id' => $payment->advert_id,
                    'type'      => 'Shipping Update',
                    'message'   => $buyerMessages[$status] ?? "The shipping status of your order has been updated to: {$status}.",
                    'is_read'   => 0,
                ]);
            } catch (\Exception $e) {
                Log::error('Shipping-status buyer in-app notification failed: ' . $e->getMessage());
            }

            try {
                SendShippingUpdatePushNotification::dispatch($buyer, $payment, $status);
            } catch (\Exception $e) {
                Log::error('Shipping-status buyer push dispatch failed: ' . $e->getMessage());
            }
        }

        if ($seller) {
            $sellerMessages = [
                'shipped'   => "Your item \"{$adTitle}\" has been shipped by {$company} and is on its way to the buyer.",
                'pickup'    => "The buyer's pickup point for \"{$adTitle}\" is ready at {$company}.",
                'delivered' => "The buyer has received \"{$adTitle}\" via {$company}. Awaiting buyer confirmation.",
                'canceled'  => "{$company} has an update on the shipment of \"{$adTitle}\". Please check the order details.",
            ];

            try {
                Notification::create([
                    'user_id'   => $seller->id,
                    'seller_id' => $seller->id,
                    'advert_id' => $payment->advert_id,
                    'type'      => 'Shipping Update',
                    'message'   => $sellerMessages[$status] ?? "The shipping status of \"{$adTitle}\" has been updated to: {$status}.",
                    'is_read'   => 0,
                ]);
            } catch (\Exception $e) {
                Log::error('Shipping-status seller in-app notification failed: ' . $e->getMessage());
            }

            try {
                SendShippingUpdatePushNotification::dispatch($seller, $payment, $status);
            } catch (\Exception $e) {
                Log::error('Shipping-status seller push dispatch failed: ' . $e->getMessage());
            }
        }
    }
}
