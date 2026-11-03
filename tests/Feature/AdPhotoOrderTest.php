<?php

namespace Tests\Feature;

use App\Models\AdSetting;
use App\Models\Advert;
use App\Models\Brands;
use App\Models\Lga;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The photo picker sends photo_order (every photo in display order), so the
 * cover can be any photo — including a new one — and a seller can replace a
 * photo in one save even at the minimum photo count.
 */
class AdPhotoOrderTest extends TestCase
{
    protected ?User $seller = null;
    protected ?string $minImagesBackup = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->minImagesBackup = AdSetting::where('key', 'min_images')->value('value');
        AdSetting::setValue('min_images', 3);
        Cache::forget('ad_setting_min_images');

        $this->seller = User::factory()->create(['phone' => '09' . random_int(10000000, 99999999)]);
    }

    protected function tearDown(): void
    {
        foreach (Advert::where('user_id', $this->seller->user_id)->get() as $ad) {
            $ad->clearMediaCollection('images');
            $ad->delete();
        }
        $this->seller->delete();

        $this->minImagesBackup === null
            ? AdSetting::where('key', 'min_images')->delete()
            : AdSetting::setValue('min_images', $this->minImagesBackup);
        Cache::forget('ad_setting_min_images');

        parent::tearDown();
    }

    /** 1200x900 noisy JPEG that passes the quality checks. */
    protected function photo(string $name): UploadedFile
    {
        $img = imagecreatetruecolor(1200, 900);
        for ($y = 0; $y < 900; $y += 4) {
            for ($x = 0; $x < 1200; $x += 4) {
                imagefilledrectangle($img, $x, $y, $x + 3, $y + 3, imagecolorallocate($img, random_int(0, 255), random_int(60, 200), random_int(0, 255)));
            }
        }
        $path = tempnam(sys_get_temp_dir(), 'photo') . '.jpg';
        imagejpeg($img, $path, 90);
        imagedestroy($img);

        return new UploadedFile($path, "{$name}.jpg", 'image/jpeg', null, true);
    }

    protected function adFields(): array
    {
        $lga = Lga::with('state')->whereHas('state')->firstOrFail();
        $brand = Brands::where('subcat_id', 234)->first();
        if (!$brand) {
            $this->markTestSkipped('Needs the seeded Fashion > Men\'s clothing brands.');
        }

        return [
            'ad_title' => 'Men cotton shirt ' . Str::random(5), 'ad_type' => 'Private', 'category' => 5, 'subcategory' => 234, 'brand' => $brand->id,
            'state' => $lga->state->name, 'lga' => $lga->name, 'price' => 2500, 'price_type' => 'Fixed',
            'item_condition' => 'New', 'description' => 'Brand new cotton shirt, size L, never worn. Original packaging.',
            'shipment' => 'Pickup', 'buy_direct' => 'No', 'show_contact' => 'Yes',
        ];
    }

    protected function photoNames(Advert $ad): array
    {
        return $ad->fresh()->getMedia('images')->sortBy('order_column')
            ->map(fn ($m) => $m->getCustomProperty('original_name'))->values()->all();
    }

    protected function postAd(array $extra): Advert
    {
        $this->withSession(['user_id' => $this->seller->user_id])
            ->post('/user/post-ad', $this->adFields() + $extra)
            ->assertSessionHasNoErrors();

        return Advert::where('user_id', $this->seller->user_id)->latest('id')->firstOrFail();
    }

    public function test_post_ad_saves_photos_in_the_chosen_order(): void
    {
        $ad = $this->postAd([
            'images' => [$this->photo('front'), $this->photo('back'), $this->photo('inside')],
            'photo_order' => 'n:2,n:0,n:1',
        ]);

        $this->assertSame(['inside.jpg', 'front.jpg', 'back.jpg'], $this->photoNames($ad));
    }

    public function test_edit_replaces_a_photo_at_the_minimum_and_new_photo_can_be_cover(): void
    {
        $ad = $this->postAd(['images' => [$this->photo('a'), $this->photo('b'), $this->photo('c')]]);
        $media = $ad->fresh()->getMedia('images')->sortBy('order_column')->values();
        $this->assertCount(3, $media);

        // Remove "a" (leaving 2 saved, below the minimum of 3) and add "d" as the new cover.
        $this->withSession(['user_id' => $this->seller->user_id])
            ->post("/user/edit-ad/{$ad->id}", $this->adFields() + [
                'images' => [$this->photo('d')],
                'deleted_images' => [$media[0]->id],
                'existing_images' => [$media[1]->id, $media[2]->id],
                'photo_order' => "n:0,s:{$media[2]->id},s:{$media[1]->id}",
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(['d.jpg', 'c.jpg', 'b.jpg'], $this->photoNames($ad));
    }

    public function test_edit_still_refuses_dropping_below_the_minimum_without_replacements(): void
    {
        $ad = $this->postAd(['images' => [$this->photo('a'), $this->photo('b'), $this->photo('c')]]);
        $first = $ad->fresh()->getMedia('images')->sortBy('order_column')->first();

        $this->withSession(['user_id' => $this->seller->user_id])
            ->post("/user/edit-ad/{$ad->id}", $this->adFields() + ['deleted_images' => [$first->id]])
            ->assertSessionHasErrors('deleted_images');

        $this->assertCount(3, $ad->fresh()->getMedia('images'));
    }
}
