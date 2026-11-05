<?php

namespace Tests\Unit;

use App\Support\PaymentReference;
use Tests\TestCase;

/**
 * The shared Flutterwave webhook router sends each event to the site named
 * by the reference's country prefix, so every reference must carry it.
 */
class PaymentReferenceTest extends TestCase
{
    public function test_references_start_with_the_site_country_code(): void
    {
        config(['services.flutterwave.site' => 'ET']);

        $this->assertMatchesRegularExpression('/^ET-MKT-[A-Z0-9]{20}$/', PaymentReference::make('MKT'));
        $this->assertMatchesRegularExpression('/^ET-BOOST-[A-Z0-9]{16}$/', PaymentReference::make('BOOST', 16));
    }

    public function test_the_prefix_follows_config_and_can_be_turned_off(): void
    {
        config(['services.flutterwave.site' => 'ke']);
        $this->assertSame('KE-', PaymentReference::prefix());

        config(['services.flutterwave.site' => '']);
        $this->assertSame('', PaymentReference::prefix());
    }
}
