<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FAQRCode\Google2FA;

class Admin2FAController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    // Show QR code for first-time setup
    public function setup(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        // Already confirmed and verified this session — go to dashboard
        if ($admin->two_factor_confirmed_at && $request->session()->get('admin_2fa_verified')) {
            return redirect()->route('admin.dashboard');
        }

        // If admin already has an unconfirmed secret stored, reuse it so refreshes
        // don't generate a new QR. Only generate a brand-new secret if none exists yet.
        if (!$admin->google2fa_secret) {
            $secret = $this->google2fa->generateSecretKey();
            // Store immediately (unconfirmed — two_factor_confirmed_at stays null)
            $admin->update(['google2fa_secret' => encrypt($secret)]);
            $admin->refresh();
        } else {
            $secret = decrypt($admin->google2fa_secret);
        }

        $qrCodeSvg = $this->google2fa->getQRCodeInline(
            config('global.site_name', 'Marketplace Ethiopia'),
            $admin->email,
            $secret,
            250
        );

        $title = 'Setup Two-Factor Authentication | ' . config('global.site_name');

        return view('admin.two-factor.setup', compact('title', 'qrCodeSvg', 'secret'));
    }

    // Confirm setup: verify the submitted code against the stored secret
    public function confirmSetup(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $admin = Auth::guard('admin')->user();

        if (!$admin->google2fa_secret) {
            return redirect()->route('admin.2fa.setup')
                ->with('error', 'Setup not started. Please scan the QR code first.');
        }

        $secret = decrypt($admin->google2fa_secret);
        $valid  = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->with('error', 'Invalid code. Please try again using your authenticator app.');
        }

        // Mark as confirmed
        $admin->update(['two_factor_confirmed_at' => now()]);

        $request->session()->put('admin_2fa_verified', true);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Two-factor authentication is now enabled.');
    }

    // Show TOTP code entry for subsequent logins
    public function verify(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin || !$admin->google2fa_secret) {
            return redirect()->route('admin.2fa.setup');
        }

        if ($request->session()->get('admin_2fa_verified')) {
            return redirect()->route('admin.dashboard');
        }

        $title = 'Two-Factor Verification | ' . config('global.site_name');

        return view('admin.two-factor.verify', compact('title'));
    }

    // Verify the TOTP code at login
    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $admin = Auth::guard('admin')->user();

        if (!$admin || !$admin->google2fa_secret) {
            return redirect()->route('admin.2fa.setup');
        }

        $secret = decrypt($admin->google2fa_secret);
        $valid  = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->with('error', 'Invalid code. Please check your authenticator app and try again.');
        }

        $request->session()->put('admin_2fa_verified', true);

        return redirect()->route('admin.dashboard');
    }
}
