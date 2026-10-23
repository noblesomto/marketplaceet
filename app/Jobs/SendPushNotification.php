<?php
// app/Jobs/SendPushNotification.php

namespace App\Jobs;

use App\Models\Message;
use App\Models\User;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected User $user;
    protected Message $message;
    protected User $sender;

    public function __construct(User $user, Message $message, User $sender)
    {
        $this->user = $user;
        $this->message = $message;
        $this->sender = $sender;
    }

    public function handle(FirebaseCloudMessagingService $fcmService): void
    {
        // Check if user can receive notifications
        if (!$this->user->canReceivePushNotifications()) {
            Log::info('User cannot receive push notifications', [
                'user_id' => $this->user->id,
            ]);
            return;
        }

        // Get all active device tokens
        $tokens = $this->user->deviceTokens->pluck('token')->toArray();

        if (empty($tokens)) {
            Log::info('No device tokens found', [
                'user_id' => $this->user->id,
            ]);
            return;
        }

        // Prepare notification
        $content = $this->message->message_content;

        if (!empty($content)) {
            $messagePreview = mb_strlen($content) > 100
                ? mb_substr($content, 0, 100) . '...'
                : $content;
        } else {
            $messagePreview = 'Sent a photo';
        }

        $notification = [
            'title' => $this->sender->name,
            'body'  => $messagePreview,
            'badge' => $this->user->unreadNotificationCount(),
        ];

        // Add advert thumbnail if available and publicly accessible
        $advert = $this->message->advert;
        $imageUrl = $advert ? ($advert->getFirstImageUrl('thumbnail') ?? '') : '';
        if (!empty($imageUrl) && str_starts_with($imageUrl, 'https://')) {
            $notification['image'] = $imageUrl;
        }

        $data = [
            'type' => 'new_message',
            'message_id' => (string) $this->message->id,
            'sender_id' => (string) $this->sender->user_id,
            'sender_name' => $this->sender->name,
            'advert_id' => (string) $this->message->advert_id,
            'timestamp' => $this->message->created_at->toIso8601String(),
            'click_action' => 'OPEN_CONVERSATION',
        ];

        // Send notification
        $result = $fcmService->sendToTokens($tokens, $notification, $data);

        Log::info('Push notification sent', [
            'user_id' => $this->user->id,
            'message_id' => $this->message->id,
            'token_count' => count($tokens),
            'success' => $result,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Push notification job failed', [
            'user_id' => $this->user->id,
            'message_id' => $this->message->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
