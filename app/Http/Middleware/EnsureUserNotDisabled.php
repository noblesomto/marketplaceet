<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserNotDisabled
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->disable_account === 'yes') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been disabled. Please contact support.',
            ], 403);
        }

        return $next($request);
    }
}
