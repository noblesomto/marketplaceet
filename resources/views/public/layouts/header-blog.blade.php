<!doctype html>
<html lang="en">
<head>
    <title>{{ $title ?? ($blog->title ?? 'Marketplace Ethiopia') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css','resources/js/app.js'])

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PVDT4VHH"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- SEO Meta -->
    <meta name="description" content="{{ $blog->meta_description ?? Str::limit(strip_tags($blog->content ?? ''), 160) }}">
    <meta name="keywords" content="{{ $blog->keywords ?? '' }}, Marketplace Ethiopia, Buy & Sell in Ethiopia, Post free ads in Ethiopia, Online marketplace Ethiopia">
    <meta name="author" content="Marketplace Ethiopia">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />


    @php
    // Default fallback image
    $defaultImage = asset('frontend/images/og-default.png');

    // Use featured image if available, otherwise default
    $featuredImage = $blog->featured_image_webp ?? $defaultImage;

    // Collect all images
    $images = [];
    if (!empty($featuredImage)) {
        $images[] = $featuredImage;
    }

    // Ensure at least one image is always available
    if (empty($images)) {
        $images[] = $defaultImage;
    }

    // Schema data
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "Article",
        "headline" => $blog->title ?? 'Marketplace Ethiopia',
        "image" => $images,
        "description" => Str::limit(strip_tags($blog->content ?? ''), 200),
        "author" => [
            "@type" => "Organization",
            "name" => "Marketplace Ethiopia"
        ],
        "publisher" => [
            "@type" => "Organization",
            "name" => "Marketplace Ethiopia",
            "logo" => [
                "@type" => "ImageObject",
                "url" => asset('frontend/images/og-default.png')
            ]
        ],
        "mainEntityOfPage" => [
            "@type" => "WebPage",
            "@id" => url()->current()
        ]
    ];
@endphp


    <!-- Open Graph -->
    <meta property="og:site_name" content="Marketplace Ethiopia">
    <meta property="og:title" content="{{ ($blog->title ?? 'Marketplace Ethiopia') . ' | Marketplace Ethiopia' }}">
    <meta property="og:description" content="{{ $blog->meta_description ?? Str::limit(strip_tags($blog->contnet ?? ''), 160) }}">
    <meta property="og:image" content="{{ $featuredImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">
    <meta property="og:locale" content="en_ET">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ ($blog->title ?? $blog->title ?? 'Marketplace Ethiopia') . ' | Marketplace Ethiopia' }}">
    <meta name="twitter:description" content="{{ $blog->meta_description ?? $blog->meta_description ?? Str::limit(strip_tags($blog->content ?? $blog->content ?? ''), 160) }}">
    <meta name="twitter:image" content="{{ $featuredImage }}">

    <!-- JSON-LD Schema -->
    <script type="application/ld+json">
    {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>


  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('frontend/images/favicon.png') }}">

  <script src='https://www.google.com/recaptcha/api.js' async defer></script>
  <link rel="canonical" href="https://marketplace.et/" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
<script type="text/javascript">
   (function(c,l,a,r,i,t,y){
       c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
       t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
       y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
   })(window, document, "clarity", "script", "su2vqghfhw");
</script>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-TPBJ5F0GJP"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-TPBJ5F0GJP');
</script>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-17541624328"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-17541624328');
</script>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4998736645213032"
     crossorigin="anonymous"></script>

    <!-- Apple Smart App Banner — shown automatically by Safari on iOS -->
    <meta name="apple-itunes-app" content="app-id=6753354778">
</head>
<body class="bg-body text-gray-700 text-sm">
    @include('public.components.app-install-banner')
