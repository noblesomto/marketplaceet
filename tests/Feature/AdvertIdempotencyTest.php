<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Advert;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\State;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

/**
 * Covers the createAdvert() idempotency-key guard added to close a
 * TOCTOU race: a client that retries POST /api/adverts after a network
 * error (e.g. connection reset while reading the response) must not end
 * up with two adverts.
 */
class AdvertIdempotencyTest extends TestCase
{
    protected $user;
    protected $category;
    protected $subCategory;
    protected $brand;
    protected $state;
    protected array $createdAdvertIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first();
        if (!$this->user) {
            $this->markTestSkipped('No users found in database. Please seed the database first.');
        }

        if (empty($this->user->phone)) {
            $this->user->phone = '08012345678';
            $this->user->save();
        }

        // Jobs category (id=3) skips the mandatory image requirement.
        $this->category = Category::find(3) ?? Category::first();
        $this->subCategory = SubCategory::where('cat_id', $this->category->id)->first()
            ?? SubCategory::first();
        $this->brand = Brands::first();
        $this->state = State::first();

        if (!$this->category || !$this->subCategory || !$this->brand || !$this->state) {
            $this->markTestSkipped('Required test data (category, subcategory, brand, state) not found. Please seed the database.');
        }
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdAdvertIds)) {
            Advert::whereIn('id', $this->createdAdvertIds)->delete();
        }

        parent::tearDown();
    }

    protected function advertPayload(string $idempotencyKey): array
    {
        return [
            'idempotency_key' => $idempotencyKey,
            'ad_title'      => 'Idempotency Test Ad ' . Str::random(8),
            'ad_type'       => 'Sell',
            'category'      => $this->category->id,
            'subcategory'   => $this->subCategory->id,
            'brand'         => $this->brand->id ?? null,
            'salary'        => 150000,
            'buy_direct'    => 'No',
            'state'         => $this->state->name,
            'lga'           => \App\Models\Lga::first()->name,
            'description'   => 'This is a test job description with more than 20 characters to pass validation.',
            'shipment'      => 'No',
            'show_contact'  => 'Yes',
        ];
    }

    /** @test */
    public function retrying_the_same_idempotency_key_replays_instead_of_duplicating()
    {
        Sanctum::actingAs($this->user, ['*']);

        $key = (string) Str::uuid();
        $payload = $this->advertPayload($key);

        $first = $this->postJson('/api/adverts', $payload);
        $first->assertStatus(201);
        $first->assertJsonPath('success', true);

        $advertId = $first->json('data.id');
        $this->createdAdvertIds[] = $advertId;

        // Simulate the client retrying the exact same request (same key) after
        // e.g. a connection reset — it must not create a second advert.
        $second = $this->postJson('/api/adverts', $payload);
        $second->assertStatus(200);
        $second->assertJsonPath('replayed', true);
        $second->assertJsonPath('data.id', $advertId);

        $this->assertSame(
            1,
            Advert::where('user_id', $this->user->user_id)
                ->where('idempotency_key', $key)
                ->count(),
            'Retrying with the same idempotency key must not create a second advert.'
        );
    }

    /** @test */
    public function the_database_rejects_a_second_advert_with_the_same_user_and_key()
    {
        $key = (string) Str::uuid();

        $advert = Advert::create(array_merge(
            ['idempotency_key' => $key, 'user_id' => $this->user->user_id],
            [
                'ad_title'   => 'Race Test A ' . Str::random(8),
                'title_slug' => Str::slug('race-test-a-' . Str::random(8)),
                'ad_type'    => 'Sell',
                'category'   => $this->category->id,
                'brand'      => $this->brand->id,
                'sub_category' => $this->subCategory->id,
                'buy_direct' => 'No',
                'description' => 'Race condition regression test advert.',
                'state'      => $this->state->name,
                'lga'        => \App\Models\Lga::first()->name,
                'views'      => '0',
                'ad_status'  => 'active',
                'source'     => 'api',
            ]
        ));
        $this->createdAdvertIds[] = $advert->id;

        // This is the scenario two near-simultaneous requests would hit: both
        // pass the SELECT-based check, then both attempt the INSERT. The unique
        // index on (user_id, idempotency_key) must be what actually stops the
        // second one, since the PHP-level check alone can't (TOCTOU).
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        Advert::create(array_merge(
            ['idempotency_key' => $key, 'user_id' => $this->user->user_id],
            [
                'ad_title'   => 'Race Test B ' . Str::random(8),
                'title_slug' => Str::slug('race-test-b-' . Str::random(8)),
                'ad_type'    => 'Sell',
                'category'   => $this->category->id,
                'brand'      => $this->brand->id,
                'sub_category' => $this->subCategory->id,
                'buy_direct' => 'No',
                'description' => 'Race condition regression test advert.',
                'state'      => $this->state->name,
                'lga'        => \App\Models\Lga::first()->name,
                'views'      => '0',
                'ad_status'  => 'active',
                'source'     => 'api',
            ]
        ));
    }

    /** @test */
    public function different_users_can_reuse_the_same_idempotency_key()
    {
        $otherUser = User::factory()->create(['phone' => '08099999999']);

        $key = (string) Str::uuid();

        $mine = Advert::create([
            'idempotency_key' => $key,
            'user_id' => $this->user->user_id,
            'ad_title' => 'Mine ' . Str::random(8),
            'title_slug' => Str::slug('mine-' . Str::random(8)),
            'ad_type' => 'Sell',
            'category' => $this->category->id,
            'brand' => $this->brand->id,
            'sub_category' => $this->subCategory->id,
            'buy_direct' => 'No',
            'description' => 'Idempotency scoping regression test advert.',
            'state'      => $this->state->name,
            'lga'        => \App\Models\Lga::first()->name,
            'views'      => '0',
            'ad_status' => 'active',
            'source' => 'api',
        ]);
        $this->createdAdvertIds[] = $mine->id;

        $theirs = Advert::create([
            'idempotency_key' => $key,
            'user_id' => $otherUser->user_id,
            'ad_title' => 'Theirs ' . Str::random(8),
            'title_slug' => Str::slug('theirs-' . Str::random(8)),
            'ad_type' => 'Sell',
            'category' => $this->category->id,
            'brand' => $this->brand->id,
            'sub_category' => $this->subCategory->id,
            'buy_direct' => 'No',
            'description' => 'Idempotency scoping regression test advert.',
            'state'      => $this->state->name,
            'lga'        => \App\Models\Lga::first()->name,
            'views'      => '0',
            'ad_status' => 'active',
            'source' => 'api',
        ]);
        $this->createdAdvertIds[] = $theirs->id;

        $this->assertNotEquals($mine->id, $theirs->id);
    }
}
