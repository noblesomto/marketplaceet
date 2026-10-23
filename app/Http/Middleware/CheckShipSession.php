<?php

namespace App\Http\Middleware;

use Closure;

class CheckShipSession
{

    public function handle($request, Closure $next)
    {
        if (!$request->session()->exists('ship_id')) {
            // user value cannot be found in session
            return redirect('/shipper');
        }

        return $next($request);
    }

}
