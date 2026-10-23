<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\AdminLoginAttempts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\HtmlString;
use App\Helpers\ContentHelper;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminPasswordResetMail;

class AdminAccount extends Controller
{
    public function adminlogin(Request $request)
{
    $title = "Admin Login - " . config('global.site_name');

    if ($request->isMethod('POST')) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        // Rate limiting
        $key = Str::lower($request->email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            RateLimiter::clear($key);

            // Log successful login
            AdminLoginAttempts::create([
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => true,
            ]);

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Welcome back, ' . Auth::guard('admin')->user()->username);
        }

        // Log failed attempt
        AdminLoginAttempts::create([
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => false,
        ]);

        RateLimiter::hit($key, 60);

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Invalid email or password.');
    }

    return view('public.account.admin', compact('title'));
}

    /**
     * Admin Forgot Password
     */
    public function forgotPassword(Request $request)
    {
        $title = "Admin Forgot Password - " . config('global.site_name');

        if ($request->isMethod('POST')) {
            $request->validate([
                'email' => 'required|email',
            ]);

            $email = $request->email;
            $admin = Admin::where('email', $email)->first();

            if ($admin) {
                $username = $admin->username;
                $admin_id = $admin->id;
                $token = Str::random(60);

                // Save the token to the database
                DB::table('admins')
                    ->where('id', $admin_id)
                    ->update([
                        'token' => $token
                    ]);

                $details = [
                    'admin_id' => $admin_id,
                    'token' => $token,
                    'username' => $username,
                ];

                try {
                    Mail::to($email)->send(new AdminPasswordResetMail($details));
                    return redirect()->route('admin.login')
                        ->with('success', 'Password reset link sent to your email. Please check your inbox.');
                } catch (\Throwable $e) {
                    return redirect()->back()
                        ->with('error', 'Email could not be sent. Please try again later.');
                }
            } else {
                return redirect()->back()
                    ->with('error', 'This email is not registered as an admin.');
            }
        }

        return view('public.account.admin-forgot-password', compact('title'));
    }

    /**
     * Admin Reset Password
     */
    public function resetPassword(Request $request, $admin_id, $token)
    {
        $title = "Admin Reset Password - " . config('global.site_name');
        $admin = Admin::find($admin_id);

        if (!$admin) {
            return redirect()->route('admin.login')
                ->with('error', 'Invalid reset link.');
        }

        $storedToken = $admin->token;

        if ($request->isMethod('GET')) {
            if ($token == $storedToken && $storedToken !== null) {
                return view('public.account.admin-reset-password', compact('title', 'admin_id', 'token'));
            } else {
                return redirect()->route('admin.login')
                    ->with('error', 'Invalid or expired reset token.');
            }
        }

        if ($request->isMethod('POST')) {
            // Verify token again
            if ($token != $storedToken) {
                return redirect()->route('admin.login')
                    ->with('error', 'Invalid or expired reset token.');
            }

            $request->validate([
                'password' => 'required|min:8|confirmed',
            ]);

            DB::table('admins')
                ->where('id', $admin_id)
                ->update([
                    'password' => Hash::make($request->input('password')),
                    'token' => null,
                ]);

            return redirect()->route('admin.login')
                ->with('success', 'Password successfully updated. Please login with your new password.');
        }
    }
}
