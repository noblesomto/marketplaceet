<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleMerchantFeedGenerator;

class GenerateGoogleFeed extends Command
{
    protected $signature = 'feed:google';
    protected $description = 'Generate Google Merchant product feed XML';

    public function handle(GoogleMerchantFeedGenerator $generator)
    {
        $path = $generator->generate();
        $this->info("✅ Google Merchant feed generated successfully at: {$path}");
    }
}
