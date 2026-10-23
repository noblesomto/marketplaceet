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

class SendFollowerPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 30;

    protected User $follower;
    protected Advert $advert;
    protected User $seller;
    protected string $notificationType;
    protected string $message;

    /**
     * Create a new job instance.
     */
    public function __construct(
        User $follower,
        Advert $advert,
        User $seller,
        string $notificationType,
        string $message
    ) {
        $this->follower = $follower;
        $this->advert = $advert;
        $this->seller = $seller;
        $this->notificationType = $notificationType;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(FirebaseCloudMessagingService $fcmService): void
    {
        // Check if follower can receive push notifications
        if (!$this->follower->canReceivePushNotifications()) {
            Log::info('Follower cannot receive push notifications', [
                'follower_id' => $this->follower->id,
                'seller_id' => $this->seller->id,
            ]);
            return;
        }

        // Get all active device tokens
        $tokens = $this->follower->deviceTokens->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        // Prepare notification based on type
        $notification = $this->prepareNotification();
        $data = $this->prepareData();

        // Send notification
        $result = $fcmService->sendToTokens($tokens, $notification, $data);

        Log::info('Follower push notification sent', [
            'follower_id' => $this->follower->id,
            'seller_id' => $this->seller->id,
            'advert_id' => $this->advert->id,
            'type' => $this->notificationType,
            'token_count' => count($tokens),
            'success' => $result,
        ]);
    }

    /**
     * Prepare notification content
     */
    protected function prepareNotification(): array
    {
        $title = $this->seller->name;
        $body = '';

        switch ($this->notificationType) {
            case 'New Ad':
                $body = "Posted a new ad: {$this->advert->ad_title}";
                break;

            case 'Price Update':
                $formattedPrice = $this->formatPrice($this->advert->price);
                $body = "Updated price for {$this->advert->ad_title} to {$formattedPrice}";
                break;

            default:
                $body = $this->message;
                break;
        }

        $notification = [
            'title' => $title,
            'body'  => $body,
            'badge' => $this->follower->unreadNotificationCount(),
        ];

        // Add advert thumbnail if available and publicly accessible
        $imageUrl = $this->advert->getFirstImageUrl('thumbnail') ?? '';
        if (!empty($imageUrl) && str_starts_with($imageUrl, 'https://')) {
            $notification['image'] = $imageUrl;
        }

        return $notification;
    }

    /**
     * Prepare notification data payload
     */
    protected function prepareData(): array
    {
        return [
            'type' => 'follower_notification',
            'notification_type' => $this->notificationType,
            'advert_id' => (string) $this->advert->id,
            'ad_id' => (string) $this->advert->ad_id,
            'seller_id' => (string) $this->seller->user_id,
            'seller_name' => $this->seller->name,
            'ad_title' => $this->advert->ad_title,
            'price' => (string) ($this->advert->price ?? ''),
            'title_slug' => $this->advert->title_slug ?? '',
            'state_slug' => $this->advert->state_slug ?? '',
            'timestamp' => now()->toIso8601String(),
            'click_action' => 'OPEN_ADVERT',
        ];
    }

    /**
     * Format price for display
     */
    protected function formatPrice($price): string
    {
        if (empty($price)) {
            return 'Contact for price';
        }

        return 'Br ' . number_format($price, 0);
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Follower push notification job failed', [
            'follower_id' => $this->follower->id,
            'seller_id' => $this->seller->id,
            'advert_id' => $this->advert->id,
            'type' => $this->notificationType,
            'error' => $exception->getMessage(),
        ]);
    }
}
