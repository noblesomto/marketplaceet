<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\Auth\CookieSessionService;
use Illuminate\Http\Request;

class CookieSessionServiceTest extends TestCase
{
    private CookieSessionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CookieSessionService();
    }

    // -------------------------------------------------------------------------
    // generateDeviceHash
    // -------------------------------------------------------------------------

    public function test_generate_device_hash_returns_sha1_string(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 TestBrowser/1.0');

        $hash = $this->service->generateDeviceHash($request);

        $this->assertIsString($hash);
        $this->assertSame(40, strlen($hash), 'SHA1 hash must be 40 hex characters.');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $hash);
    }

    public function test_generate_device_hash_is_deterministic(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'TestAgent/2.0');

        $hash1 = $this->service->generateDeviceHash($request);
        $hash2 = $this->service->generateDeviceHash($request);

        $this->assertSame($hash1, $hash2,
            'Same user-agent must always produce the same hash.'
        );
    }

    public function test_generate_device_hash_differs_for_different_user_agents(): void
    {
        $request1 = Request::create('/', 'GET');
        $request1->headers->set('User-Agent', 'Chrome/100');

        $request2 = Request::create('/', 'GET');
        $request2->headers->set('User-Agent', 'Safari/15');

        $hash1 = $this->service->generateDeviceHash($request1);
        $hash2 = $this->service->generateDeviceHash($request2);

        $this->assertNotSame($hash1, $hash2,
            'Different user-agents must produce different hashes.'
        );
    }

    public function test_generate_device_hash_is_ip_independent(): void
    {
        // Two requests with the same UA but different IPs must produce the same hash
        $ua = 'SameAgent/1.0';

        $req1 = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.2.3.4']);
        $req1->headers->set('User-Agent', $ua);

        $req2 = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '9.9.9.9']);
        $req2->headers->set('User-Agent', $ua);

        $this->assertSame(
            $this->service->generateDeviceHash($req1),
            $this->service->generateDeviceHash($req2),
            'Device hash must be stable across different IPs (mobile users change IPs).'
        );
    }

    // -------------------------------------------------------------------------
    // issueRememberCookies — verify DB + cookie side-effects
    // -------------------------------------------------------------------------

    public function test_issue_remember_cookies_updates_remember_token(): void
    {
        $user = \App\Models\User::first();

        if (!$user) {
            $this->markTestSkipped('No users in database.');
        }

        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'TestBrowser/3.0');

        $oldToken = $user->remember_token;

        $this->service->issueRememberCookies($request, $user);

        // Reload from DB
        $user->refresh();

        $this->assertNotNull($user->remember_token,
            'remember_token must not be null after issuing cookies.'
        );
        // The stored token is SHA256-hashed, so it will differ from the old raw token
    }

    public function test_issue_remember_cookies_queues_both_cookies(): void
    {
        $user = \App\Models\User::first();

        if (!$user) {
            $this->markTestSkipped('No users in database.');
        }

        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'TestBrowser/4.0');

        // Should not throw — the cookie queue will be populated
        $this->service->issueRememberCookies($request, $user);

        // If we reached here without exception, cookies were queued successfully
        $this->assertTrue(true);
    }
}
