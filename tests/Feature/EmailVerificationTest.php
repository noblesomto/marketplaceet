<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Regression coverage for the "verify your email" loop: resending the
 * activation email used to overwrite `users.token`, silently invalidating
 * every previously sent link, and clicking a link after the account was
 * already verified returned a confusing "token does not match" error
 * instead of a friendly "already verified" message.
 */
class EmailVerificationTest extends TestCase
{
    protected array $createdUserIds = [];

    protected function tearDown(): void
    {
        if (!empty($this->createdUserIds)) {
            User::whereIn('id', $this->createdUserIds)->delete();
        }

        parent::tearDown();
    }

    protected function makeUnverifiedUser(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'acc_status' => 0,
            'token' => Str::random(40),
        ], $overrides));

        $this->createdUserIds[] = $user->id;

        return $user;
    }

    /** @test */
    public function resending_the_web_activation_email_does_not_invalidate_a_previously_sent_link()
    {
        $user = $this->makeUnverifiedUser();
        $originalToken = $user->token;

        $this->get('/resend-email?email=' . urlencode($user->email));

        $user->refresh();
        $this->assertSame(
            $originalToken,
            $user->token,
            'Resending the activation email must not invalidate a link already sent to the user.'
        );
    }

    /** @test */
    public function visiting_the_web_verification_link_again_after_already_verified_shows_success()
    {
        $user = $this->makeUnverifiedUser();
        $staleToken = $user->token;

        // Simulate the account having already been verified (e.g. via a newer email).
        $user->update(['acc_status' => 1, 'token' => null]);

        $response = $this->get('/verifyaccount/' . urlencode($user->email) . '/' . $staleToken);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');
        $response->assertSessionMissing('error');
    }

    /** @test */
    public function resending_the_api_verification_email_does_not_invalidate_a_previously_sent_link()
    {
        $user = $this->makeUnverifiedUser();
        $originalToken = $user->token;

        $this->postJson('/api/resend-verification', ['email' => $user->email]);

        $user->refresh();
        $this->assertSame(
            $originalToken,
            $user->token,
            'Resending the activation email must not invalidate a link already sent to the user.'
        );
    }

    /** @test */
    public function visiting_the_api_verification_link_again_after_already_verified_returns_success()
    {
        $user = $this->makeUnverifiedUser();
        $staleToken = $user->token;

        $user->update(['acc_status' => 1, 'token' => null]);

        $response = $this->getJson('/api/verify/' . urlencode($user->email) . '/' . $staleToken);

        $response->assertStatus(200);
        $response->assertJsonPath('status', true);
    }
}
