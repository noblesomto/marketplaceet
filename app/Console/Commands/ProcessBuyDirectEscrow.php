<?php

namespace App\Console\Commands;

use App\Services\BuyDirectEscrow;
use Illuminate\Console\Command;

class ProcessBuyDirectEscrow extends Command
{
    protected $signature = 'buydirect:process';

    protected $description = 'Auto-cancel and refund Buy Direct orders not shipped in time, auto-release confirmed-window orders, and sync in-flight refunds/payouts with Flutterwave';

    public function handle(): int
    {
        $stats = BuyDirectEscrow::make()->processDue();

        $this->info(sprintf(
            'Canceled: %d, released: %d, refunds synced: %d, payouts synced: %d',
            $stats['canceled'], $stats['released'], $stats['refunds_synced'], $stats['payouts_synced']
        ));

        return self::SUCCESS;
    }
}
