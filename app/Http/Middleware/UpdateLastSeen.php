<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Keeps users.last_seen_at fresh for genuinely active users, web or API,
 * as opposed to last_login_at which only moves on a fresh login/token event.
 */
class UpdateLastSeen
{
    /**
     * Minimum minutes between DB writes for the same user.
     */
    protected const THROTTLE_MINUTES = 5;

    public function handle(Request $request, Closure $next)
    {
        $userId = null;

        if ($request->hasSession() && $request->session()->has('user_id')) {
            $userId = $request->session()->get('user_id');
        } elseif ($apiUser = auth('sanctum')->user()) {
            $userId = $apiUser->user_id;
        }

        if ($userId) {
            $this->touch($userId);
        }

        return $next($request);
    }

    protected function touch($userId): void
    {
        $cacheKey = "last_seen_throttle_{$userId}";

        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, now()->addMinutes(self::THROTTLE_MINUTES));

        // Query builder update, not an Eloquent model save(), so it doesn't
        // also bump users.updated_at on every page view/API call.
        DB::table('users')->where('user_id', $userId)->update(['last_seen_at' => now()]);
    }
}
