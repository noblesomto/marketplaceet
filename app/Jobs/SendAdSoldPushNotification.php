<?php

namespace App\Jobs;

use App\Models\Advert;
use App\Models\User;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAdSoldPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected User $owner;
    protected Advert $advert;
    protected string $buyerName;

    public function __construct(User $owner, Advert $advert, string $buyerName)
    {
        $this->owner     = $owner;
        $this->advert    = $advert;
        $this->buyerName = $buyerName;
    }

    public function handle(FirebaseCloudMessagingService $fcmService): void
    {
        if (!$this->owner->canReceivePushNotifications()) {
            return;
        }

        $tokens = $this->owner->deviceTokens->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $title = 'Your item has been sold!';
        $body  = "{$this->buyerName} just purchased \"{$this->advert->ad_title}\".";

        $notification = [
            'title' => $title,
            'body'  => $body,
            'badge' => $this->owner->unreadNotificationCount(),
        ];

        $imageUrl = $this->advert->getFirstImageUrl('thumbnail') ?? '';
        if (!empty($imageUrl) && str_starts_with($imageUrl, 'https://')) {
            $notification['image'] = $imageUrl;
        }

        $data = [
            'type'              => 'ad_sold',
            'advert_id'         => (string) $this->advert->id,
            'ad_id'             => (string) $this->advert->ad_id,
            'ad_title'          => $this->advert->ad_title,
            'buyer_name'        => $this->buyerName,
            'title_slug'        => $this->advert->title_slug ?? '',
            'state_slug'        => $this->advert->state_slug ?? '',
            'timestamp'         => now()->toIso8601String(),
            // Mobile: open shipping details screen for this advert
            'click_action'      => 'OPEN_AD_SHIPPING',
            'shipping_api_url'  => '/api/user/adverts/' . $this->advert->id . '/shipping-details',
            'shipping_web_url'  => '/user/ad-shipping/' . $this->advert->id,
        ];

        $result = $fcmService->sendToTokens($tokens, $notification, $data);

        Log::info('Ad sold push notification sent', [
            'owner_id'  => $this->owner->id,
            'advert_id' => $this->advert->id,
            'buyer'     => $this->buyerName,
            'success'   => $result,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Ad sold push notification job failed', [
            'owner_id'  => $this->owner->id,
            'advert_id' => $this->advert->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}
