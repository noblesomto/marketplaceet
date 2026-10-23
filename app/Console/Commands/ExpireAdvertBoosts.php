<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpireAdvertBoosts extends Command
{
    protected $signature = 'boosts:expire';
    protected $description = 'Mark expired advert boosts as completed and unfeature adverts';

    public function handle()
    {
        $now = Carbon::now();

        // 1️⃣ Expire all active boosts whose end date has passed
        $expiredBoosts = DB::table('advert_boosts')
            ->where('boost_status', 'active')
            ->where('payment_status', 'paid')
            ->whereRaw("DATE_ADD(start_date, INTERVAL duration DAY) <= ?", [$now])
            ->pluck('advert_id'); // collect affected advert IDs

        if ($expiredBoosts->isEmpty()) {
            $this->info('No expired boosts found.');
            return Command::SUCCESS;
        }

        // Update boosts to completed
        DB::table('advert_boosts')
            ->whereIn('advert_id', $expiredBoosts)
            ->where('boost_status', 'active')
            ->where('payment_status', 'paid')
            ->whereRaw("DATE_ADD(start_date, INTERVAL duration DAY) <= ?", [$now])
            ->update(['boost_status' => 'completed']);

        // 2️⃣ Unfeature adverts ONLY if they have no other still-active boost
        $stillBoosted = DB::table('advert_boosts')
            ->where('boost_status', 'active')
            ->where('payment_status', 'paid')
            ->whereRaw("DATE_ADD(start_date, INTERVAL duration DAY) > ?", [$now])
            ->whereIn('advert_id', $expiredBoosts)
            ->pluck('advert_id');

        $toUnfeature = $expiredBoosts->diff($stillBoosted);

        if ($toUnfeature->isNotEmpty()) {
            DB::table('adverts')
                ->whereIn('id', $toUnfeature)
                ->update(['featured' => 'No']);
        }

        $this->info(count($expiredBoosts) . ' boost(s) expired, ' . $toUnfeature->count() . ' advert(s) unfeatured.');

        return Command::SUCCESS;
    }
}
