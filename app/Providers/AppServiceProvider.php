<?php

namespace App\Providers;

use App\Services\MediaImageService;
use Illuminate\Support\ServiceProvider;
use App\Services\FirebaseCloudMessagingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MediaImageService::class, function ($app) {
            return new MediaImageService();
        });
        $this->app->singleton(FirebaseCloudMessagingService::class, function ($app) {
            return new FirebaseCloudMessagingService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Scribe will auto-register routes when add_routes is true
        // No need to do anything here
    }
}
