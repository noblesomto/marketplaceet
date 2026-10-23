<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AccountController;

class AutoLoginFromCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // ✅ FIX: Don't auto-login if:
        // 1. Already logged in (has user_id)
        // 2. Waiting for OTP verification (has acc_id or otp_pending flag)
        $waitingForOtp = $request->session()->has('acc_id') || $request->session()->has('otp_pending');

        if (!$request->session()->has('user_id') && !$waitingForOtp) {
            AccountController::autoLoginFromCookie($request);
        }

        return $next($request);
    }
}
