<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetCacheHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only cache static assets
        $path = $request->path();

        if (preg_match('/\.(js|css|woff2?|ttf|otf|png|jpe?g|webp|svg|gif|ico|mp3|mp4)$/i', $path)) {
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
            $response->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }

        return $response;
    }
}
