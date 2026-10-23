<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TrustedDevice;

class CleanupTrustedDevices extends Command
{
    protected $signature = 'cleanup:trusted-devices';
    protected $description = 'Remove expired trusted devices';

    public function handle()
    {
        $deleted = TrustedDevice::where('expires_at', '<', now())->delete();
        $this->info("Deleted {$deleted} expired trusted devices");
    }
}
