<?php
namespace App\Services;

use App\Models\Advert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class GoogleMerchantFeedGenerator
{
    // Keyed by category ID (string) → Google Product Taxonomy string.
    // null = category is not eligible for Google Shopping (e.g. Real Estate).
    protected array $googleTaxonomyMap = [
        '1'  => 'Vehicles & Parts',
        '2'  => 'Electronics',
        '3'  => null, // Jobs — not a shoppable product
        '4'  => 'Electronics > Communications > Telephones > Mobile Phones',
        '5'  => 'Apparel & Accessories',
        '7'  => null, // Real Estate — not accepted by Google Shopping
        '8'  => 'Health & Beauty',
        '9'  => 'Furniture',
        '10' => 'Sporting Goods',
        '11' => null, // Services — not a shoppable product
        '12' => 'Business & Industrial',
        '13' => null, // Repair & Construction — not a shoppable product
        '14' => null, // Animal & Pets — Google bans sale of live animals
        '16' => 'Baby & Toddler',
        '17' => 'Food, Beverages & Tobacco',
        '18' => null, // Seeking Work / CVs — not a shoppable product
    ];

    public function generate(): string
    {
        // Pre-load all categories keyed by id to avoid N+1 queries
        $categories = DB::table('categories')
            ->select('id', 'category', 'category_slug')
            ->get()
            ->keyBy('id');

        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>'
        );

        $channel = $xml->addChild('channel');
        $channel->addChild('title', config('app.name') . ' Product Feed');
        $channel->addChild('link', url('/'));
        $channel->addChild('description', 'Latest adverts from ' . config('app.name'));

        $adverts = Advert::where('ad_status', 'active')
            ->where('sold', 'No')
            ->whereRaw("LOWER(TRIM(buy_direct)) = 'yes'")
            ->orderByDesc('created_at')
            ->limit(5000)
            ->get();

        $included = 0;
        $skipped  = 0;

        foreach ($adverts as $ad) {
            $categoryId = (string) $ad->category;

            // Skip categories not in the allow-list, or explicitly mapped to null
            if (!array_key_exists($categoryId, $this->googleTaxonomyMap)
                || $this->googleTaxonomyMap[$categoryId] === null) {
                $skipped++;
                continue;
            }

            // Skip unparseable or zero price
            $priceValue = (float) preg_replace('/[^\d.]/', '', $ad->price ?? '');
            if ($priceValue <= 0) {
                $skipped++;
                continue;
            }

            // Skip if the product link would be malformed
            if (empty($ad->state_slug) || empty($ad->title_slug) || empty($ad->ad_id)) {
                $skipped++;
                continue;
            }

            $item = $channel->addChild('item');

            $item->addChild('id', htmlspecialchars((string) $ad->ad_id), 'http://base.google.com/ns/1.0');

            $title = mb_substr(strip_tags($ad->ad_title ?? 'Untitled Product'), 0, 150);
            $item->addChild('title', htmlspecialchars($title));

            $description = mb_substr(strip_tags($ad->description ?? 'No description available.'), 0, 5000);
            $item->addChild('description', htmlspecialchars($description));

            $item->addChild('link', url("{$ad->state_slug}/{$ad->title_slug}/{$ad->ad_id}"));

            // Image — use 'optimized' (≥250×250); 'thumbnail' is 200×200 which fails Google's minimum
            $imageUrl = $ad->hasMedia('images')
                ? $ad->getFirstMediaUrl('images', 'optimized')
                : asset('frontend/images/default.png');
            $item->addChild('image_link', $imageUrl, 'http://base.google.com/ns/1.0');

            $item->addChild('price', number_format($priceValue, 2, '.', '') . ' ETB', 'http://base.google.com/ns/1.0');

            // All queried ads are unsold — always 'in stock'
            $item->addChild('availability', 'in stock', 'http://base.google.com/ns/1.0');

            // Condition
            $condition = strtolower(trim($ad->item_condition ?? ''));
            if (!in_array($condition, ['new', 'refurbished', 'used'])) {
                $condition = 'used';
            }
            $item->addChild('condition', $condition, 'http://base.google.com/ns/1.0');

            // Marketplace items have no GTIN/MPN — required to prevent disapproval for missing identifiers
            $item->addChild('identifier_exists', 'no', 'http://base.google.com/ns/1.0');

            if (!empty($ad->brand)) {
                $item->addChild('brand', htmlspecialchars($ad->brand), 'http://base.google.com/ns/1.0');
            }

            // product_type — human-readable category name for campaign segmentation in Google Ads
            $catName = $categories->get((int) $categoryId)?->category ?? null;
            if ($catName) {
                $item->addChild('product_type', htmlspecialchars($catName), 'http://base.google.com/ns/1.0');
            }

            // google_product_category — only set when we have a confident mapping
            $googleCat = $this->googleTaxonomyMap[$categoryId] ?? null;
            if ($googleCat) {
                $item->addChild('google_product_category', htmlspecialchars($googleCat), 'http://base.google.com/ns/1.0');
            }

            // All items in this feed are buy_direct — label for Google Ads bidding
            $item->addChild('custom_label_0', 'buy_direct', 'http://base.google.com/ns/1.0');

            // Shipping
            $shipping = $item->addChild('shipping', null, 'http://base.google.com/ns/1.0');
            $shipping->addChild('country', 'ET', 'http://base.google.com/ns/1.0');

            $shipment = strtolower(trim($ad->shipment ?? ''));
            if ($shipment === 'pickup') {
                $shipping->addChild('service', 'Pickup', 'http://base.google.com/ns/1.0');
            } elseif ($shipment === 'ship') {
                $shipping->addChild('service', 'Standard Shipping', 'http://base.google.com/ns/1.0');
            } else {
                $shipping->addChild('service', 'Standard', 'http://base.google.com/ns/1.0');
            }
            $shipping->addChild('price', '0.00 ETB', 'http://base.google.com/ns/1.0');

            $included++;
        }

        $filePath = 'feeds/google_merchant.xml';
        Storage::disk('public')->put($filePath, $xml->asXML());

        \Illuminate\Support\Facades\Log::info('Google Merchant feed generated', [
            'included' => $included,
            'skipped'  => $skipped,
            'path'     => $filePath,
        ]);

        return storage_path('app/public/uploads/' . $filePath);
    }
}
