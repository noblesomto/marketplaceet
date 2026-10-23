<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePhoneComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && empty($user->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Please add your phone number before posting an advert.',
            ], 403);
        }

        return $next($request);
    }
}
