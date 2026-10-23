<!doctype html>
<html lang="en">

    @php
    if (!function_exists('cleanMetaText')) {
    function cleanMetaText($text) {
        if (empty($text)) return '';

        // Step 1: Replace literal HTML entity strings FIRST (before decoding)
        $text = str_replace(
            ['&nbsp;', '&amp;', '&lt;', '&gt;', '&quot;', '&#39;', '&apos;'],
            [' ', '&', '<', '>', '"', "'", "'"],
            $text
        );

        // Step 2: Decode HTML entities multiple times (handles actual encoded entities)
        $iterations = 0;
        $previousText = '';
        while ($text !== $previousText && $iterations < 5) {
            $previousText = $text;
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $iterations++;
        }

        // Step 3: Strip all HTML/XML tags
        $text = strip_tags($text);

        // Step 4: Replace common HTML tag strings that might be literal text
        $text = str_replace(
            ['<br>', '<br/>', '<br />', '</br>', '<p>', '</p>', '<div>', '</div>'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
            $text
        );

        // Step 5: Remove any remaining HTML entity patterns (both &#xxx; and &name;)
        $text = preg_replace('/&[a-zA-Z0-9#]+;/', ' ', $text);

        // Step 6: Replace multiple whitespace (spaces, tabs, newlines) with single space
        $text = preg_replace('/\s+/', ' ', $text);

        // Step 7: Clean up common spacing issues around punctuation
        $text = preg_replace('/\s+([,.!?;:])/', '$1', $text);

        // Step 8: Remove special characters that don't belong in meta tags
        $text = preg_replace('/[^\p{L}\p{N}\s\-.,!?$€£¥&@()\'\"]/u', '', $text);

        // Step 9: Final trim
        return trim($text);
    }
    }

    // Clean title
    $cleanTitle = cleanMetaText($ad->ad_title ?? 'Marketplace Ethiopia');

    // Clean description (meta_description takes priority, falls back to description)
    $rawDescription = $ad->meta_description ?? $ad->description ?? '';
    $cleanDescription = Str::limit(cleanMetaText($rawDescription), 160);

    // Clean keywords
    $cleanKeywords = cleanMetaText($ad->keyword ?? '');

    // For Schema and OG tags
    $featuredImage = $ad->getSocialImageUrl();
    $allImages = $ad->getAllImagesForSchema();
    $imageDimensions = $ad->getSocialImageDimensions();

    // Schema data with cleaned text
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "Product",
        "name" => $cleanTitle,
        "image" => $allImages,
        "description" => Str::limit(cleanMetaText($rawDescription), 200),
        "brand" => [
            "@type" => "Brand",
            "name" => $ad->brands->brand ?? "Marketplace Ethiopia"
        ],
        "offers" => [
            "@type" => "Offer",
            "url" => url()->current(),
            "priceCurrency" => "ETB",
            "price" => $ad->price ?? '0.00',
            "availability" => $ad->sold == 'Yes' ? "https://schema.org/OutOfStock" : "https://schema.org/InStock",
            "itemCondition" => "https://schema.org/" . ($ad->item_condition == 'New' ? 'NewCondition' : 'UsedCondition')
        ]
    ];
@endphp

<head>
    <title>{{ $cleanTitle }} | Marketplace Ethiopia</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css','resources/js/app.js'])

    <!-- SEO Meta -->
    <meta name="description" content="{{ $cleanDescription }}">
    <meta name="keywords" content="{{ $cleanKeywords }}">
    <meta name="author" content="Marketplace Ethiopia">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />

    <!-- Open Graph Meta Tags -->
    <meta property="og:site_name" content="Marketplace Ethiopia">
    <meta property="og:title" content="{{ $cleanTitle }} | Marketplace Ethiopia">
    <meta property="og:description" content="{{ $cleanDescription }}">
    <meta property="og:image" content="{{ $featuredImage }}">
    <meta property="og:image:secure_url" content="{{ $featuredImage }}">
    <meta property="og:image:width" content="{{ $imageDimensions['width'] }}">
    <meta property="og:image:height" content="{{ $imageDimensions['height'] }}">
    <meta property="og:image:alt" content="{{ $cleanTitle }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="product">
    <meta property="og:locale" content="en_ET">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $cleanTitle }} | Marketplace Ethiopia">
    <meta name="twitter:description" content="{{ $cleanDescription }}">
    <meta name="twitter:image" content="{{ $featuredImage }}">
    <meta name="twitter:image:alt" content="{{ $cleanTitle }}">

    <!-- Additional Product Meta -->
    <meta property="product:price:amount" content="{{ $ad->price ?? '0.00' }}">
    <meta property="product:price:currency" content="ETB">
    <meta property="product:availability" content="{{ $ad->sold == 'Yes' ? 'out of stock' : 'in stock' }}">
    @if($ad->item_condition)
    <meta property="product:condition" content="{{ strtolower($ad->item_condition) }}">
    @endif

    <!-- JSON-LD Schema -->
    <script type="application/ld+json">
    {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    @include('public.layouts.header-links')


