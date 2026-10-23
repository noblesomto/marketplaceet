<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdvertBoost;
use Illuminate\Support\Facades\DB;

class MarkExpiredBoosts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mark:expired-boosts';

    /**
     * The console command description.
     */
    protected $description = 'Mark expired advert boosts as completed and unfeature the adverts.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DB::beginTransaction();

        try {
            $expiredBoosts = AdvertBoost::with('advert')
                ->where('boost_status', 'active')
                ->where('payment_status', 'paid')
                ->whereNotNull('start_date')
                ->whereHas('advert')
                ->whereRaw("DATE_ADD(start_date, INTERVAL CAST(duration AS UNSIGNED) DAY) <= ?", [now()])
                ->get();

            foreach ($expiredBoosts as $boost) {
                $boost->boost_status = 'completed';
                $boost->save();

                if ($boost->advert) {
                    $boost->advert->featured = 'No';
                    $boost->advert->save();
                }
            }

            DB::commit();

            $this->info("{$expiredBoosts->count()} boost(s) updated.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
