<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin2FAVerified
{
    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Secret not stored yet, or stored but not yet confirmed — force setup
        if (!$admin->google2fa_secret || !$admin->two_factor_confirmed_at) {
            return redirect()->route('admin.2fa.setup');
        }

        // Confirmed but not verified this session — enter code
        if (!$request->session()->get('admin_2fa_verified')) {
            return redirect()->route('admin.2fa.verify');
        }

        return $next($request);
    }
}
