<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Bank;

class SyncBanks extends Command
{
    protected $signature = 'banks:sync';
    protected $description = 'Sync Ethiopian banks from Flutterwave API';

    public function handle()
    {
        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->get(config('services.flutterwave.paymentUrl') . '/banks/ET');

        if (!$response->ok()) {
            $this->error('Failed to fetch banks from Flutterwave.');
            return 1;
        }

        $banks = $response->json('data');

        if (empty($banks)) {
            $this->error('Flutterwave returned no banks — check FLUTTERWAVE_SECRET_KEY.');
            return 1;
        }

        foreach ($banks as $bank) {
            Bank::updateOrCreate(
                ['bank_code' => $bank['code']],
                ['name' => $bank['name']]
            );
        }

        $this->info('Bank list synced successfully (' . count($banks) . ' banks).');
        return 0;
    }
}
