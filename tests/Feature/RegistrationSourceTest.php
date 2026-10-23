<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;

/**
 * Admin needs to know whether an account was created via the web form, the
 * mobile app's API, or a social login — captured in users.source at the
 * point of registration.
 */
class RegistrationSourceTest extends TestCase
{
    protected array $createdUserIds = [];

    protected function tearDown(): void
    {
        if (!empty($this->createdUserIds)) {
            User::whereIn('id', $this->createdUserIds)->delete();
        }

        parent::tearDown();
    }

    /** @test */
    public function registering_through_the_web_form_records_the_source_as_web()
    {
        $email = 'web-signup-' . uniqid() . '@example.com';

        $this->post('/register', [
            'acc_type' => 'Private',
            'name' => 'Web Signup',
            'phone' => '091' . random_int(1000000, 9999999),
            'email' => $email,
            'password' => 'password123',
        ]);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user, 'Web registration should have created a user.');
        $this->createdUserIds[] = $user->id;

        $this->assertSame('web', $user->source);
    }

    /** @test */
    public function registering_through_the_api_records_the_source_as_app()
    {
        $email = 'api-signup-' . uniqid() . '@example.com';

        $this->postJson('/api/register', [
            'acc_type' => 'Private',
            'name' => 'App Signup',
            'phone' => '092' . random_int(1000000, 9999999),
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user, 'API registration should have created a user.');
        $this->createdUserIds[] = $user->id;

        $this->assertSame('app', $user->source);
    }

    /** @test */
    public function signing_in_with_google_for_the_first_time_records_the_source_as_google()
    {
        $email = 'google-signup-' . uniqid() . '@example.com';

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getEmail')->andReturn($email);
        $socialiteUser->shouldReceive('getName')->andReturn('Google Signup');
        $socialiteUser->shouldReceive('getNickname')->andReturn('googlesignup');
        $socialiteUser->shouldReceive('getId')->andReturn('google-123456');

        $driver = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $driver->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

        $this->get('/auth/google/callback');

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user, 'Social login should have created a user.');
        $this->createdUserIds[] = $user->id;

        $this->assertSame('google', $user->source);
    }
}
