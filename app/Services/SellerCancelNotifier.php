<?php

namespace App\Services;

use App\Jobs\SendShippingUpdatePushNotification;
use App\Mail\OrderCanceledRefundMail;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Buyer notification + push, and admin refund email, when an order is
 * canceled on the seller's side (seller_status = canceled). Shared by the
 * seller's own shipping page and the admin payment editor.
 */
class SellerCancelNotifier
{
    public static function notify(Payment $payment): void
    {
        $payment->loadMissing(['advert.user', 'user']);

        $buyer   = $payment->user;
        $seller  = $payment->advert->user ?? null;
        $adTitle = $payment->advert->ad_title ?? 'your order';

        if ($buyer) {
            try {
                Notification::create([
                    'user_id'   => $buyer->id,
                    'seller_id' => $seller->id ?? $buyer->id,
                    'advert_id' => $payment->advert_id,
                    'type'      => 'Order Canceled',
                    'message'   => "The seller canceled your order for \"{$adTitle}\". Your refund is being processed.",
                    'is_read'   => 0,
                ]);
            } catch (\Exception $e) {
                Log::error('Seller-cancel in-app notification (buyer) failed: ' . $e->getMessage());
            }

            try {
                SendShippingUpdatePushNotification::dispatch($buyer, $payment, 'seller_canceled');
            } catch (\Exception $e) {
                Log::error('Seller-cancel push notification (buyer) dispatch failed: ' . $e->getMessage());
            }
        }

        try {
            $details = [
                'order_code'   => $payment->order_code ?? $payment->id,
                'advert'       => $adTitle,
                'amount_paid'  => $payment->amount_paid ?? 0,
                'reference'    => $payment->payment_reference ?? '—',
                'seller_name'  => $seller->name ?? '—',
                'seller_email' => $seller->email ?? '—',
                'buyer_name'   => trim(($payment->first_name ?? '') . ' ' . ($payment->last_name ?? '')) ?: ($buyer->name ?? '—'),
                'buyer_phone'  => $payment->phone ?? '—',
                'buyer_email'  => $buyer->email ?? '—',
                'canceled_by'  => 'Seller',
            ];

            Mail::to(config('global.admin_email'))->queue(new OrderCanceledRefundMail($details));
        } catch (\Exception $e) {
            Log::error('Seller-cancel admin refund email failed: ' . $e->getMessage());
        }
    }
}
