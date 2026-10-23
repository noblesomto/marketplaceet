<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LowercaseLocationUrl
{
    /**
     * Redirect any location/category/brand/advert URL containing uppercase
     * characters to its lowercase equivalent, so search engines never index
     * both cases of the same page under different canonicals.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        if ($path !== strtolower($path)) {
            $query = $request->getQueryString();
            $target = url(strtolower($path)) . ($query ? '?' . $query : '');

            return redirect($target, 301);
        }

        return $next($request);
    }
}
