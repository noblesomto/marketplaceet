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

        $this->assertMatchesRegularExpression('/^ET-MKT-[A-Z0-9]{20}$/', PaymentReference::charge());
        $this->assertMatchesRegularExpression('/^ET-MKT-PAYOUT-42-3-[A-Z0-9]{6}$/', PaymentReference::payout(42, 3));
        $this->assertMatchesRegularExpression('/^ET-BOOST-[A-Z0-9]{16}$/', PaymentReference::boostRequest());
    }

    public function test_the_prefix_follows_config(): void
    {
        config(['services.flutterwave.site' => ' ke ']);
        $this->assertSame('KE-', PaymentReference::prefix());
    }

    public function test_a_missing_or_invalid_site_falls_back_to_the_default(): void
    {
        foreach ([null, '', 'ETH', 'E-T', '12'] as $site) {
            config(['services.flutterwave.site' => $site]);
            $this->assertSame(PaymentReference::DEFAULT_SITE . '-', PaymentReference::prefix(), var_export($site, true));
        }
    }

    public function test_it_recognises_another_sites_references(): void
    {
        config(['services.flutterwave.site' => 'ET']);

        $this->assertTrue(PaymentReference::isForAnotherSite('KE-MKT-ABC'));
        $this->assertTrue(PaymentReference::isForAnotherSite('GH-MKT-PAYOUT-1-1-ABCDEF'));

        // Ours, including references created before prefixes existed
        $this->assertFalse(PaymentReference::isForAnotherSite('ET-MKT-ABC'));
        $this->assertFalse(PaymentReference::isForAnotherSite('MKT-ABC'));
        $this->assertFalse(PaymentReference::isForAnotherSite('MKT-PAYOUT-1-1-ABCDEF'));
        $this->assertFalse(PaymentReference::isForAnotherSite('boost-1730800000000'));
        $this->assertFalse(PaymentReference::isForAnotherSite(null));
    }
}
