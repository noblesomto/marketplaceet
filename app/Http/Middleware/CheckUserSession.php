<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CheckUserSession
{
    public function handle(Request $request, Closure $next): Response
    {
        // ✅ If session exists, verify account is still active
        if ($request->session()->has('user_id')) {
            $user = DB::table('users')
                ->where('user_id', $request->session()->get('user_id'))
                ->first();

            // Check if account is disabled, unverified, or doesn't exist
            if (!$user || $user->disable_account === 'yes') {
                $this->clearUserSession($request);

                return redirect('/login')
                    ->with('error', 'Your account has been deactivated or no longer exists.');
            }

            if ($user->acc_status == 0) {
                $this->clearUserSession($request);

                return redirect('/login')
                    ->with('error', new \Illuminate\Support\HtmlString(
                        'Please verify your email address before continuing. ' .
                        '<a href="' . route('activation.resend', ['email' => $user->email]) . '" class="text-blue-600 underline">Resend verification email</a>'
                    ));
            }

            // New social sign-ups without a phone are flagged at login and must
            // complete their profile before doing anything else on the site.
            if ($request->session()->get('must_complete_phone') && empty($user->phone)) {
                $redirect = $this->enforcePhoneCompletion($request);
                if ($redirect) {
                    return $redirect;
                }
            }

            return $next($request);
        }

        // ✅ If no session, try auto-login via cookie
        if ($request->hasCookie('remember_login')) {
            $token = $request->cookie('remember_login');
            $user = DB::table('users')
                ->where('remember_token', hash('sha256', $token))
                ->first();

            if ($user) {
                // Check if account is disabled or unverified
                if ($user->disable_account === 'yes') {
                    $this->clearUserSession($request);

                    return redirect('/login')
                        ->with('error', 'Your account has been deactivated.');
                }

                if ($user->acc_status == 0) {
                    $this->clearUserSession($request);

                    return redirect('/login')
                        ->with('error', new \Illuminate\Support\HtmlString(
                            'Please verify your email address before continuing. ' .
                            '<a href="' . route('activation.resend', ['email' => $user->email]) . '" class="text-blue-600 underline">Resend verification email</a>'
                        ));
                }

                // Restore session
                $request->session()->put('user_id', $user->user_id);
                $request->session()->put('name', $user->name);

                // Refresh cookie expiry (rolling 90 days)
                Cookie::queue(Cookie::make(
                    'remember_login',
                    $token,
                    60 * 24 * 90, // 90 days
                    '/',
                    null,
                    $request->secure(),  // ✅ FIXED: Dynamic based on HTTPS
                    true,  // httpOnly
                    false, // raw
                    'Lax' // sameSite
                ));

                return $next($request);
            } else {
                // Invalid/expired cookie - clear it
                Cookie::queue(Cookie::forget('remember_login'));
            }
        }

        // ✅ If everything fails → force login with intended URL
        // Only store intended URL for GET requests that aren't AJAX
        if ($request->isMethod('GET') && !$request->ajax() && !$request->wantsJson()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect('/login');
    }

    /**
     * Redirect page navigations to the profile-update page until the phone
     * number is added. AJAX/JSON requests are left alone so background
     * scripts on the profile page itself don't break.
     */
    private function enforcePhoneCompletion(Request $request): ?Response
    {
        $exemptRoutes = ['user.profile.update', 'user.profile.phone', 'user.logout'];

        if ($request->route() && in_array($request->route()->getName(), $exemptRoutes)) {
            return null;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return null;
        }

        if ($request->isMethod('GET')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()->route('user.profile.update')
            ->with('profile_required', 'Please add your phone number to continue.');
    }

    /**
     * Clear user session and cookies
     */
    private function clearUserSession(Request $request): void
    {
        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear remember cookie
        Cookie::queue(Cookie::forget('remember_login'));
    }
}
