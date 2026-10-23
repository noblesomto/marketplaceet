<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only set CSP on HTML responses
        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $self   = "'self'";
        $none   = "'none'";
        $inline = "'unsafe-inline'";
        $eval   = "'unsafe-eval'";

        $googleFonts   = 'fonts.googleapis.com fonts.gstatic.com';
        $cdn           = 'cdn.jsdelivr.net cdnjs.cloudflare.com';
        $tailwind      = 'cdn.tailwindcss.com';
        $tinymce       = 'cdn.tiny.cloud sp.tinymce.com';
        $gtm           = 'www.googletagmanager.com googletagmanager.com';
        $googleAds     = 'pagead2.googlesyndication.com tpc.googlesyndication.com googleads.g.doubleclick.net';
        $gstatic       = 'www.gstatic.com';
        $recaptcha     = 'www.google.com';
        $clarity       = 'www.clarity.ms c.bing.com';
        $pusher        = 'js.pusher.com stats.pusher.com wss://*.pusher.com';
        $firebase      = 'www.googleapis.com firebaseinstallations.googleapis.com fcmregistrations.googleapis.com';
        $analytics     = 'www.google-analytics.com region1.google-analytics.com';

        $directives = implode('; ', [
            "default-src {$self}",

            // Scripts: self + all third-party JS we load + unsafe-inline for Blade scripts
            "script-src {$self} {$inline} {$eval} {$gtm} {$gstatic} {$recaptcha} {$cdn} {$clarity} {$pusher} {$firebase} {$analytics} {$googleAds} {$tailwind} {$tinymce} blob:",

            // Styles: self + Google Fonts + CDN + inline (Bootstrap, etc.)
            "style-src {$self} {$inline} {$googleFonts} {$cdn} {$tailwind} cdnjs.cloudflare.com",

            // Images: self + data URIs + Google QR codes + analytics pixels + CDN images
            "img-src {$self} data: blob: https://chart.googleapis.com https://lh3.googleusercontent.com https://graph.facebook.com *.googleusercontent.com {$analytics} {$googleAds} www.googletagmanager.com",

            // Fonts: self + Google Fonts + CDN
            "font-src {$self} {$googleFonts} {$cdn} cdnjs.cloudflare.com data:",

            // Connects: self + Pusher websocket + Firebase + analytics + Flutterwave
            "connect-src {$self} wss://*.pusher.com https://*.pusher.com {$firebase} {$analytics} {$gtm} https://api.flutterwave.com https://checkout.flutterwave.com",

            // Frames: Google reCAPTCHA, Flutterwave checkout
            "frame-src {$self} {$recaptcha} https://checkout.flutterwave.com",

            // Workers: self + blob (Firebase SW uses blob worker)
            "worker-src {$self} blob:",

            // Manifests
            "manifest-src {$self}",

            // Lockdowns
            "object-src {$none}",
            "base-uri {$self}",
            "form-action {$self}",
        ]);

        $response->headers->set('Content-Security-Policy', $directives);

        return $response;
    }
}
