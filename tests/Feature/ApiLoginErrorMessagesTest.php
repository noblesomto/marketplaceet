<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

/**
 * Regression coverage for account enumeration via POST /api/login: the
 * endpoint used to say "Email address does not exist." for an unregistered
 * email but "Incorrect password." for a registered one, letting a caller
 * probe which emails have accounts. Both cases must now be indistinguishable.
 */
class ApiLoginErrorMessagesTest extends TestCase
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
    public function login_with_an_unregistered_email_does_not_reveal_that_the_account_does_not_exist()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'definitely-not-registered-' . uniqid() . '@example.com',
            'password' => 'whatever1',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Invalid email or password.');
    }

    /** @test */
    public function login_with_the_wrong_password_returns_the_identical_generic_message()
    {
        $user = User::factory()->create(['acc_status' => 1]);
        $this->createdUserIds[] = $user->id;

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'definitely-wrong-password',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Invalid email or password.');
    }
}
