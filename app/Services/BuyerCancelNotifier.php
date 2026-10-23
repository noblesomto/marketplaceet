<?php

namespace App\Services;

use App\Jobs\SendShippingUpdatePushNotification;
use App\Mail\OrderCanceledRefundMail;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Seller notification + push, and admin refund email, when a buyer cancels
 * their own order (buyer_status = canceled) before it has shipped. Mirrors
 * SellerCancelNotifier but addresses the other party.
 */
class BuyerCancelNotifier
{
    public static function notify(Payment $payment): void
    {
        $payment->loadMissing(['advert.user', 'user']);

        $buyer   = $payment->user;
        $seller  = $payment->advert->user ?? null;
        $adTitle = $payment->advert->ad_title ?? 'the order';

        if ($seller) {
            try {
                Notification::create([
                    'user_id'   => $seller->id,
                    'seller_id' => $seller->id,
                    'advert_id' => $payment->advert_id,
                    'type'      => 'Order Canceled',
                    'message'   => "The buyer canceled their order for \"{$adTitle}\". No shipping is required — a refund is being processed.",
                    'is_read'   => 0,
                ]);
            } catch (\Exception $e) {
                Log::error('Buyer-cancel in-app notification (seller) failed: ' . $e->getMessage());
            }

            try {
                SendShippingUpdatePushNotification::dispatch($seller, $payment, 'buyer_canceled');
            } catch (\Exception $e) {
                Log::error('Buyer-cancel push notification (seller) dispatch failed: ' . $e->getMessage());
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
                'canceled_by'  => 'Buyer',
            ];

            Mail::to(config('global.admin_email'))->queue(new OrderCanceledRefundMail($details));
        } catch (\Exception $e) {
            Log::error('Buyer-cancel admin refund email failed: ' . $e->getMessage());
        }
    }
}
