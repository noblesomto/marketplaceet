<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\User;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendShippingUpdatePushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected User $buyer;
    protected Payment $payment;
    protected string $status;
    protected ?array $message;

    /**
     * @param array|null $message Optional [title, body] overriding the built-in copy for $status.
     */
    public function __construct(User $buyer, Payment $payment, string $status, ?array $message = null)
    {
        $this->buyer   = $buyer;
        $this->payment = $payment;
        $this->status  = $status;
        $this->message = $message;
    }

    public function handle(FirebaseCloudMessagingService $fcmService): void
    {
        if (!$this->buyer->canReceivePushNotifications()) {
            return;
        }

        $tokens = $this->buyer->deviceTokens->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $productName = $this->payment->advert->ad_title ?? 'Your order';
        $company     = $this->payment->shipping?->company ?? 'the shipping company';

        [$title, $body] = $this->message ?? match ($this->status) {
            'shipped'   => [
                'Your order has been shipped!',
                "\"{$productName}\" has been shipped by {$company} and is on its way. Tap to track.",
            ],
            'pickup'    => [
                'Your order is ready for pickup!',
                "\"{$productName}\" is ready to collect at your nearest {$company} centre.",
            ],
            'delivered' => [
                'Your order has been delivered!',
                "{$company} has delivered \"{$productName}\". Please confirm receipt.",
            ],
            'canceled'  => [
                'Shipping update for your order',
                "{$company} has an update on \"{$productName}\". Tap to view details.",
            ],
            'seller_canceled' => [
                'Your order has been canceled',
                "The seller canceled your order for \"{$productName}\". Your refund is being processed.",
            ],
            'buyer_canceled' => [
                'Order canceled by buyer',
                "The buyer canceled their order for \"{$productName}\". No shipping is required.",
            ],
            default     => [
                'Shipping update for your order',
                "The shipping status of \"{$productName}\" has been updated by {$company}.",
            ],
        };

        $notification = [
            'title' => $title,
            'body'  => $body,
            'badge' => $this->buyer->unreadNotificationCount(),
        ];

        $imageUrl = $this->payment->advert?->getFirstImageUrl('thumbnail') ?? '';
        if (!empty($imageUrl) && str_starts_with($imageUrl, 'https://')) {
            $notification['image'] = $imageUrl;
        }

        $data = [
            'type'                  => 'shipping_update',
            'shipping_status'       => $this->status,
            'payment_id'            => (string) $this->payment->id,
            'advert_id'             => (string) ($this->payment->advert_id ?? ''),
            'ad_title'              => $productName,
            'timestamp'             => now()->toIso8601String(),
            // Mobile: open the buyer's order details screen
            'click_action'          => 'OPEN_ORDER_DETAILS',
            'order_details_api_url' => '/api/user/payments/' . $this->payment->id . '/details',
            'order_details_web_url' => '/user/order-details/' . $this->payment->id,
        ];

        $result = $fcmService->sendToTokens($tokens, $notification, $data);

        Log::info('Shipping update push notification sent', [
            'buyer_id'   => $this->buyer->id,
            'payment_id' => $this->payment->id,
            'status'     => $this->status,
            'success'    => $result,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Shipping update push notification job failed', [
            'buyer_id'   => $this->buyer->id,
            'payment_id' => $this->payment->id,
            'error'      => $exception->getMessage(),
        ]);
    }
}
