<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeviceToken;

class CleanupDeviceTokens extends Command
{
    protected $signature = 'cleanup:device-tokens';
    protected $description = 'Remove inactive and stale (90+ days unused) device tokens';

    public function handle()
    {
        $cutoff = now()->subDays(90);

        $deleted = DeviceToken::where('is_active', false)
            ->orWhere('last_used_at', '<', $cutoff)
            ->orWhere(function ($query) use ($cutoff) {
                $query->whereNull('last_used_at')
                      ->where('created_at', '<', $cutoff);
            })
            ->delete();

        $this->info("Deleted {$deleted} inactive/stale device tokens");
    }
}
