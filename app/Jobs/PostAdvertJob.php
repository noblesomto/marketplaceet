<?php
// app/Jobs/PostAdvertJob.php

namespace App\Jobs;

use App\Models\Advert;
use App\Models\Followers;
use App\Models\Notification;
use App\Models\User;
use App\Mail\NewAdMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PostAdvertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $advert;
    protected $seller;
    protected $type;
    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(Advert $advert, User $seller, string $type, string $message)
    {
        $this->advert  = $advert;
        $this->seller  = $seller;
        $this->type    = $type;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $followers = Followers::with('user')
            ->where('follow', $this->seller->user_id)
            ->get();

        $now = now();
        $notifications = [];

        foreach ($followers as $follower) {
            // Skip if the follower's user account was deleted
            if (!$follower->user) {
                continue;
            }

            // Create in-app notification
            $notifications[] = [
                'user_id'    => $follower->user->id,
                'seller_id'  => $this->seller->id,
                'advert_id'  => $this->advert->id,
                'type'       => $this->type,
                'message'    => $this->message,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Send email per follower
            try {
                Mail::to($follower->user->email)->queue(
                    new NewAdMail([
                        'advert'     => $this->advert->ad_title,
                        'state_slug' => $this->advert->state_slug,
                        'title_slug' => $this->advert->title_slug,
                        'ad_id'      => $this->advert->ad_id,
                        'name'       => $follower->user->name,
                        'sellerName' => $this->seller->name,
                        'type'       => $this->type,
                    ])
                );
            } catch (\Exception $e) {
                Log::error('PostAdvertJob: failed to queue email', [
                    'follower_id' => $follower->user->id,
                    'error'       => $e->getMessage(),
                ]);
            }

            // Send push notification
            try {
                SendFollowerPushNotification::dispatch(
                    $follower->user,
                    $this->advert,
                    $this->seller,
                    $this->type,
                    $this->message
                );
            } catch (\Exception $e) {
                Log::error('PostAdvertJob: failed to dispatch push notification', [
                    'follower_id' => $follower->user->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        // Insert all in-app notifications at once
        if (!empty($notifications)) {
            Notification::insert($notifications);
        }

        // Log summary
        Log::info('PostAdvertJob completed', [
            'advert_id' => $this->advert->id,
            'seller_id' => $this->seller->id,
            'type' => $this->type,
            'follower_count' => count($followers),
        ]);
    }
}
