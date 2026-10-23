<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\User\UserProfile;
use App\Http\Controllers\Shop\ShipperController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Rules\ReCaptcha;
use Mail;
use Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\Shipping;
use App\Mail\RegisterMail;
use App\Mail\OTPMail;
use App\Mail\PasswordMail;
use App\Models\AdminLoginAttempts;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Rules\EthiopianPhoneNumber;
use App\Rules\AllowedName;
use Illuminate\Support\HtmlString;
use App\Helpers\ContentHelper;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Traits\HasUserSession;
use App\Services\Auth\CookieSessionService;
use App\Support\ActivityLog;


class AccountController extends Controller
{
    use HasUserSession;

    public function __construct(private CookieSessionService $cookieService) {}
    public function login(Request $request)
    {
        if ($request->isMethod('GET')) {
            return $this->showLoginForm();
        }

        return $this->processLoginAttempt($request);
    }

    protected function showLoginForm()
    {
        $title = "Login | " . config('global.site_name');
        return view('public.account.login', compact('title'));
    }

    protected function processLoginAttempt(Request $request)
    {
        $request->validate($this->loginValidationRules());

        $user = $this->getUserByEmail($request->email);

        if (!$this->isValidUser($user)) {
            return $this->failedLoginResponse($user);
        }

        if (!$this->isValidPassword($user, $request->password)) {
            ActivityLog::record('auth', 'Failed login attempt (wrong password)', $user, $user, [
                'ip' => $request->ip(),
                'source' => 'web',
            ]);
            return redirect('/login')->with('error', 'Sorry, the password does not match.');
        }

        return $this->loginUser($request, $user);
    }

    protected function loginValidationRules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];
    }

    protected function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    protected function isValidUser($user)
    {
        return $user && $user->acc_status == 1 && $user->disable_account !== 'yes';
    }

    protected function failedLoginResponse($user)
    {
        if (!$user) {
            return redirect()->back()->with('error', 'Sorry, the email address does not exist.');
        }

        if ($user->disable_account === 'yes') {
            return redirect()->back()->with('error', 'Sorry, this account has been disabled. Contact admin.');
        }

        if ($user->acc_status == 0) {
            return redirect()->back()->with('error', new HtmlString(
                'Sorry, the email address is not verified. ' .
                '<a href="'.route('activation.resend', ['email' => $user->email]).'" class="text-blue-600 underline">Resend activation email</a>'
            ));
        }

        return redirect()->back()->with('error', 'Invalid login attempt.');
    }

    protected function isValidPassword($user, $password)
    {
        return Hash::check($password, $user->password);
    }

    protected function isTrustedDevice($user, Request $request)
    {
        $deviceHash = $this->generateDeviceHash($request);

        $trustedDevice = $user->trustedDevices()
            ->where('device_hash', $deviceHash)
            ->where('expires_at', '>=', now())
            ->first();

        // fallback: check cookie
        if (!$trustedDevice && $request->hasCookie('trusted_device')) {
            $cookieHash = $request->cookie('trusted_device');
            $trustedDevice = $user->trustedDevices()
                ->where('device_hash', $cookieHash)
                ->where('expires_at', '>=', now())
                ->first();
        }

        if ($trustedDevice) {
            $trustedDevice->update(['last_used_at' => now()]);
            return true;
        }

        return false;
    }

    protected function loginUser(Request $request, $user)
    {
        $request->session()->put('user_id', $user->user_id);
        $request->session()->put('name', $user->name);

        // Always issue persistent cookies — "Keep me signed in" is checked by default.
        // Users who unchecked it on a shared device won't have the checkbox in the request.
        $wantsRemember = $request->has('remember_device');
        $this->storeTrustedDevice($request, $user);
        $this->cookieService->issueRememberCookies($request, $user, $wantsRemember);

        // Update login activity
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'last_login_ip' => $this->getIp(),
                'last_login_at' => now(),
            ]);

        ActivityLog::record('auth', 'Logged in', $user, $user, [
            'ip' => $this->getIp($request),
            'source' => 'web',
        ]);

        // Redirect to intended URL or default profile page
        return redirect()->intended(action([UserProfile::class, 'profile']));
    }

    protected function triggerOtpLogin(Request $request, $user)
    {
        $otp = random_int(111111, 999999);
        $request->session()->put('acc_id', $user->id);

        // ✅ ADDED: Flag to prevent auto-login during OTP verification
        $request->session()->put('otp_pending', true);

        // ✅ SECURITY: Store IP address for validation during OTP verification
        $request->session()->put('otp_ip', $request->ip());

        $request->session()->put('remember_device', $request->has('remember_device'));

        $this->storeOtp($user, $otp);

        // ✅ CRITICAL FIX: Clear any existing remember_login cookie to prevent OTP bypass
        // This ensures the auto-login middleware won't log them in while waiting for OTP
        cookie()->queue(cookie()->forget('remember_login'));

        return $this->sendOtpEmail($user, $otp);
    }

    protected function storeOtp($user, $otp)
    {
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10), // ✅ SECURITY: OTP expires in 10 minutes
            ]);
    }

    protected function sendOtpEmail($user, $otp)
    {
        $details = [
            'user_id' => $user->user_id,
            'otp' => $otp,
            'name' => $user->name,
            'ip' => $this->getIp(),
        ];

        try {
            Mail::to($user->email)->send(new OTPMail($details));
            return redirect('/authenticate')->with('success', 'Check your email for OTP to login.');
        } catch (\Throwable $e) {
            // ✅ ADDED: Log the error for debugging
            Log::error('OTP Email Failed', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            return redirect('/login')->with('error', 'Error! OTP could not be sent. Try again or contact admin.');
        }
    }

    protected function generateDeviceHash(Request $request): string
    {
        return $this->cookieService->generateDeviceHash($request);
    }

    protected function storeTrustedDevice(Request $request, $user)
    {
        $deviceHash = $this->generateDeviceHash($request);

        $user->trustedDevices()->updateOrCreate(
            ['device_hash' => $deviceHash],
            [
                'ip_address' => $this->getIp(),
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
                'expires_at' => now()->addDays(90), // ✅ CHANGED: 90 days instead of 30
            ]
        );
    }

    // ✅ IMPROVED: Auto-login from remember cookie with better security
    public static function autoLoginFromCookie(Request $request)
    {
        // Skip if already logged in
        if ($request->session()->has('user_id')) {
            return null;
        }

        // ✅ ADDED: Skip if waiting for OTP verification
        if ($request->session()->has('acc_id') || $request->session()->has('otp_pending')) {
            return null;
        }

        if ($request->hasCookie('remember_login')) {
            $token = $request->cookie('remember_login');
            $user = User::where('remember_token', hash('sha256', $token))
                ->where('acc_status', 1)
                ->where(function($query) {
                    $query->whereNull('disable_account')
                          ->orWhere('disable_account', '!=', 'yes');
                })
                ->first();

            if ($user) {
                $request->session()->put('user_id', $user->user_id);
                $request->session()->put('name', $user->name);

                // ✅ FIXED: Set longer session lifetime
                config(['session.lifetime' => 10080]); // 7 days

                // Update last login
                DB::table('users')
                    ->where('user_id', $user->user_id)
                    ->update([
                        'last_login_at' => now(),
                        'last_login_ip' => $request->ip(),
                    ]);

                // ✅ FIXED: Refresh cookie with proper secure flag
                $isSecure = $request->secure();
                cookie()->queue(cookie(
                    'remember_login',
                    $token,
                    60 * 24 * 90,         // 90 days
                    '/',
                    null,
                    $isSecure,            // Dynamic based on HTTPS
                    true,                 // httpOnly
                    false,                // raw
                    'Lax'                 // sameSite
                ));

                return $user;
            } else {
                // ✅ ADDED: Clear invalid cookie
                cookie()->queue(cookie()->forget('remember_login'));
            }
        }
        return null;
    }


    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Login failed, please try again.');
        }

        $socialEmail = $socialUser->getEmail();

        // Some Facebook accounts have no email (privacy settings) — cannot create account without one
        if (!$socialEmail) {
            return redirect('/login')->with('error', 'Your Facebook account has no email address. Please grant email permission or register with your email directly.');
        }

        // Try to find user by email
        $user = User::where('email', $socialEmail)->first();
        $isNewUser = false;

        if (!$user) {
            // If no user exists, create new one (status active by default)
            $user = User::create([
                'name'       => $socialUser->getName() ?? $socialUser->getNickname(),
                'email'      => $socialEmail,
                $provider . '_id' => $socialUser->getId(),
                'acc_status' => 1, // mark verified
                'acc_type'=> "Private",
                'source' => $provider,
                'password'   => bcrypt(Str::random(16)), // random password
            ]);
            $isNewUser = true;

            ActivityLog::record('auth', "Registered via {$provider}", $user, $user, ['source' => $provider]);
        } else {
            // Update provider ID if missing
            if (!$user->{$provider . '_id'}) {
                $user->update([
                    $provider . '_id' => $socialUser->getId(),
                ]);
            }
        }

        // Use your existing login process (skip password + OTP since provider is trusted)
        return $this->loginSocialUser($user, $isNewUser);
    }

    protected function loginSocialUser($user, bool $isNewUser = false)
    {
        // Put the same session values as normal login
        session()->put('user_id', $user->user_id);
        session()->put('name', $user->name);

        // ✅ ADDED: Set longer session lifetime for social login too
        config(['session.lifetime' => 10080]); // 7 days

        // Update login activity
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'last_login_ip' => $this->getIp(),
                'last_login_at' => now(),
            ]);

        ActivityLog::record('auth', 'Logged in via social login', $user, $user, ['ip' => $this->getIp()]);

        // Social sign-up never collects a phone number — force new accounts to add
        // one before they can use the site (existing accounts are left alone here;
        // they're caught later at checkout by the profile.complete middleware).
        if ($isNewUser && empty($user->phone)) {
            session()->put('must_complete_phone', true);

            return redirect()->route('user.profile.update')
                ->with('profile_required', 'Welcome! Please add your phone number to finish setting up your account.');
        }

        return redirect()->intended(action([UserProfile::class, 'profile']));
    }


    public function authenticate(Request $request)
    {
        $title = "OTP Authentication | " . config('global.site_name');


        if ($request->isMethod('POST')) {
            $request->validate([
                'otp' => 'required|numeric|min:4',
            ]);

            $user_id = $request->session()->get('acc_id');

            if($user_id ==''){
                return redirect("/login")->with('error','Sorry, Your Session has expired. Refresh');
            }

            // ✅ SECURITY 1: IP Address Validation - verify IP hasn't changed
            $otpIp = $request->session()->get('otp_ip');
            if ($otpIp && $otpIp !== $request->ip()) {
                $request->session()->forget(['acc_id', 'otp_pending', 'otp_ip']);
                return redirect('/login')->with('error', 'Security error: Your IP address changed. Please login again.');
            }

            // ✅ SECURITY 2: Rate Limit OTP Attempts (max 5 attempts)
            $cacheKey = 'otp_attempts:' . $user_id;
            $attempts = Cache::get($cacheKey, 0);

            if ($attempts >= 5) {
                return redirect("/authenticate")->with('error', 'Too many failed attempts. Please request a new OTP.');
            }

            $otp = $request->otp;

            // ✅ SECURITY 3: Check OTP expiration
            $login = User::where('otp', $otp)
                        ->where('id', $user_id)
                        ->where('otp_expires_at', '>=', now()) // Check expiration
                        ->first();

            if ($login) {
                $request->session()->put('user_id', $login->user_id);
                $request->session()->put('name', $login->name);

                // ✅ Clear OTP pending flag after successful verification
                $request->session()->forget('otp_pending');
                $request->session()->forget('acc_id');
                $request->session()->forget('otp_ip');

                // ✅ SECURITY 4: Clear OTP from database after successful use
                DB::table('users')
                    ->where('user_id', $login->user_id)
                    ->update([
                        'otp' => null,
                        'otp_expires_at' => null,
                        'last_login_ip' => $this->getIp(),
                        'last_login_at' => now(),
                    ]);

                // ✅ Clear failed attempt counter on success
                Cache::forget($cacheKey);

                // Always issue persistent cookies after OTP verification too
                $wantsRemember = $request->session()->get('remember_device', true);
                $this->storeTrustedDevice($request, $login);
                $this->cookieService->issueRememberCookies($request, $login, $wantsRemember);

                ActivityLog::record('auth', 'Logged in (OTP verified)', $login, $login, ['ip' => $this->getIp($request)]);

                return redirect()->intended(action([UserProfile::class, 'profile']));
            } else {
                // ✅ SECURITY 2: Increment failed attempt counter
                Cache::put($cacheKey, $attempts + 1, now()->addHour()); // 1 hour expiry

                $remainingAttempts = 5 - $attempts - 1;

                if ($remainingAttempts > 0) {
                    return redirect("/authenticate")->with('error', 'Invalid or expired OTP. ' . $remainingAttempts . ' attempt(s) remaining.');
                } else {
                    return redirect("/authenticate")->with('error', 'Too many failed attempts. Please request a new OTP.');
                }
            }


        }

        if ($request->isMethod('GET')) {
            // ✅ SECURITY: Verify user has valid OTP session before showing page
            $user_id = $request->session()->get('acc_id');

            if (!$user_id) {
                return redirect("/login")->with('error', 'Session expired. Please login again to receive OTP.');
            }

            return view('public.account.authenticate', compact('title'));
        }
    }


    public function account_status(Request $request)
    {
        $title = "Account Status  " . config('global.site_title');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        return view('public.account.account-status', compact('title','user'));
    }


    public function resend_otp(Request $request)
    {
        $user_id = $request->session()->get('acc_id');

        if (!$user_id) {
            return redirect("/login")->with('error', 'Session expired. Please login again.');
        }

        // ✅ SECURITY: Rate limit OTP resend requests (max 3 per hour)
        $rateLimitKey = 'resend-otp:' . $user_id;

        if (Cache::has($rateLimitKey) && Cache::get($rateLimitKey) >= 3) {
            return redirect("/authenticate")->with('error', 'Too many OTP requests. Please try again in 1 hour.');
        }

        $login = User::where('id', $user_id)->first();

        if (!$login) {
            return redirect("/login")->with('error', 'User not found. Please login again.');
        }

        $otp = random_int(111111, 999999);
        $email = $login->email;
        $name = $login->name;
        DB::table('users')
            ->where('id', $user_id)
            ->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(15), // ✅ SECURITY: OTP expires in 10 minutes
            ]);

        // ✅ SECURITY: Clear failed OTP attempts when new OTP is sent
        Cache::forget('otp_attempts:' . $user_id);

        // ✅ SECURITY: Increment resend counter
        $currentCount = Cache::get($rateLimitKey, 0);
        Cache::put($rateLimitKey, $currentCount + 1, now()->addHour());

        $details = [
            'user_id' => $user_id,
            'otp' => $otp,
            'name' => $name,
            'ip' => $this->getIp(),
        ];

        try {
            Mail::to($email)->send(new OTPMail($details));
            return redirect("/authenticate")->with('success', 'New OTP sent! Check your email. Code expires in 10 minutes.');
        } catch (\Throwable $e) {
            Log::error('Resend OTP failed', [
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ]);
            return redirect("/authenticate")->with('error', 'Error! OTP could not be sent. Please try again or contact admin.');
        }
    }

    public function register(Request $request, $id = null)
    {
        $title = "Create an Account | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        if ($request->isMethod('GET')) {
            return view('public.account.register', compact('title', 'user'));
        }

        if ($request->isMethod('POST')) {
            try {
                // Build validation rules
                $rules = [
                    'acc_type' => 'required',
                    'name' => ['required', 'string', 'max:100', new AllowedName],
                    'phone' => [
                        'required',
                        new EthiopianPhoneNumber(),
                    ],
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:6',
                ];

                $messages = [];

                // Add reCAPTCHA validation only if enabled
                if (config('services.recaptcha.enabled', false)) {
                    $rules['g-recaptcha-response'] = 'required';
                    $messages['g-recaptcha-response.required'] = 'Security verification is required.';
                }

                $validatedData = $request->validate($rules, $messages);

                // Verify reCAPTCHA v3 only if enabled
                if (config('services.recaptcha.enabled', false)) {
                    $recaptchaResponse = $request->input('g-recaptcha-response');
                    $recaptchaSecret = config('services.recaptcha.secret_key');

                    $verifyResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => $recaptchaSecret,
                        'response' => $recaptchaResponse,
                        'remoteip' => $request->ip()
                    ]);

                    $recaptchaData = $verifyResponse->json();

                    // Check if verification was successful and score is acceptable
                    if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.5) {
                        Log::warning('reCAPTCHA verification failed', [
                            'email' => $validatedData['email'],
                            'score' => $recaptchaData['score'] ?? 'N/A',
                            'ip' => $request->ip(),
                            'error_codes' => $recaptchaData['error-codes'] ?? []
                        ]);

                        return redirect()->back()
                            ->withInput($request->except('password'))
                            ->withErrors(['g-recaptcha-response' => 'Security verification failed. Please try again.']);
                    }
                }

            } catch (ValidationException $e) {
                throw $e;
            }

            $email = $validatedData['email'];
            $token = Str::random(40);

            DB::beginTransaction();
            try {
                $user = User::create([
                    'name' => ContentHelper::sanitizeName($validatedData['name']),
                    'email' => $validatedData['email'],
                    'phone' => $validatedData['phone'],
                    'acc_type' => $validatedData['acc_type'],
                    'source' => 'web',
                    'token' => $token,
                    'acc_status' => 0,
                    'password' => Hash::make($validatedData['password']),
                ]);

                $details = [
                    'user_id' => $email,
                    'token' => $token,
                    'name' => $validatedData['name'],
                ];

                Mail::to($email)->queue(new RegisterMail($details));

                ActivityLog::record('auth', 'Registered via web', $user, $user, ['source' => 'web']);

                DB::commit();

                return redirect("login")->with([
                    'success' => 'Great, you have successfully registered. Check your email to activate your account. Please also check your spam folder',
                    'resend_email' => $email
                ]);

            } catch (\Exception $e) {
                DB::rollBack();

                // Better error logging and user feedback
                Log::error('Registration failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'ip' => $request->ip()
                ]);

                // Check if it's an email sending error
                if ($e instanceof \Swift_TransportException ||
                    stripos($e->getMessage(), 'mail') !== false) {
                    return redirect("register")
                        ->withInput($request->except('password'))
                        ->with('error', 'Your account was created but the activation email could not be sent. Please contact admin.');
                }

                return redirect("register")
                    ->withInput($request->except('password'))
                    ->with('error', 'Registration failed. Please try again or contact admin if the problem persists.');
            }
        }
    }




    public function resend_email(Request $request)
    {
        $email = $request->query('email');

        $user = User::where('email', $email)->where('acc_status', 0)->first();

        if (!$user) {
            return redirect('login')->with('error', 'Invalid or already verified account.');
        }

        // Reuse the existing token when one is already pending so a resend doesn't
        // invalidate a link the user already received in an earlier email.
        $token = $user->token ?: Str::random(40);
        if (!$user->token) {
            $user->update(['token' => $token]);
        }

        $details = [
            'user_id' => $user->email,
            'token'   => $token,
            'name'    => $user->name,
        ];

        try {

            Mail::to($user->email)->queue(new RegisterMail($details));

            return redirect('login')->with('success', 'We have resent your activation email. Please check your inbox and spam folder');
        } catch (\Exception $e) {
            Log::error('Resend email failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return redirect('login')->with('error', 'Failed to resend activation email. Please try again or contact admin.');
        }
    }

    public function verifyaccount($user_id, $token)
    {
        $user = User::where('email', $user_id)->first();

        if (!$user) {
            return redirect("/login")->with('error','Invalid verification link');
        }

        // Account already verified (e.g. via a newer link, or this one clicked twice) —
        // treat as success instead of failing on the now-cleared token.
        if ($user->acc_status == 1) {
            return redirect("/login")->with('success','Your Email Is verified, Please Login!');
        }

        $token2 = $user->token;

        if($token == $token2){
            $post = DB::table('users')
            ->where('email', $user_id)
            ->update([
                'acc_status'=> 1,
                'token' => null,
            ]);

            return redirect("/login")->with('success','Your Email Is verified, Please Login!');
        }else{
            return redirect("/login")->with('error','Error!, the token does not match');
        }

    }



    public function forgot_password(Request $request)
    {
        $title = "Forgot Password" . config('global.site_title');

        if ($request->isMethod('POST')) {
            $request->validate([
                'email' => 'required|email',
            ]);

            $email = $request->email;
            $login = User::where('email', $email)->first();

            if ($login) {
                $name = $login->name;
                $user_id = $login->user_id;
                $token = Str::random(40);


                // Save the token to the database
                DB::table('users')
                    ->where('user_id', $user_id)
                    ->update([
                        'token' => $token
                    ]);

                $details = [
                    'user_id' => $user_id,
                    'token' => $token,
                    'name' => $name,
                ];

                try {
                    Mail::to($email)->send(new PasswordMail($details));
                    return redirect("login")->with('success', 'Please check your email for link to change password');
                } catch (Throwable $e) {
                    return redirect()->back()->with('error', 'Sorry!, Email could not be sent now. Try again later');
                }
            } else {
                return redirect()->back()->with('error', 'Sorry!, This email does not exist on our system... Please register');
            }
        }

        if ($request->isMethod('GET')) {
            return view('public.account.forgot-password', compact('title'));
        }
    }

    public function reset_password(Request $request, $user_id, $token)
    {
        $title = "Reset Password" . config('global.site_title');
        $user = User::where('user_id', $user_id)->first();
        $token2 = $user->token;

        $post = [
                'user_id' => $user_id,
                'token' => $token,
            ];

        //dd($user);
        if ($request->isMethod('GET')) {

            if($token == $token2){
                return view('public.account.reset-password', compact('title','post'));
            }else{
                return redirect("login")->with('error','Sorry!, There was an error and token does not match, Please contact admin ');
            }
        }

        if ($request->isMethod('POST')) {
            $request->validate([
                'password' => 'required|min:6|confirmed',
            ]);

            $user = DB::table('users')
            ->where('user_id', $user_id)
            ->update([
                'password'=> Hash::make($request->input('password')),
                'token' => null,
            ]);

            return redirect("login")->with('success','Your password was successfully updated, Please Login');
        }

    }

    protected function getIp(?Request $request = null)
    {
        if ($request) {
            return $request->ip();
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }




    public function adminlogin2(Request $request)
{
    $title = "Admin Login - " . config('global.site_name');

    // Handle Login POST
    if ($request->isMethod('POST')) {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {

            // Store admin ID in session
            $request->session()->put('admin_id', $admin->id);

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Welcome back, ' . $admin->username);
        }

        return back()->with('error', 'Invalid email or password.');
    }

    // GET: Show Login Form
    return view('public.account.admin', compact('title'));
}

    public function shipper(Request $request)
{
    $title = "Shipper Login - " . config('global.site_name');

    if ($request->isMethod('POST')) {
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:8',
        ]);

        $username = $request->username;
        $password = $request->password;

        $login = Shipping::where('username', $username)
           ->first();
        if ($login) {
            if(Hash::check($password, $login->password)){
                $request->session()->put('ship_id', $login->ship_id);

                return redirect()->action([ShipperController::class, 'index']);
            }else{
                return redirect()->back()->with('error', 'Error!, Your credentials are not correct');
            }
        }

        return redirect()->back()->with('error', 'Error!, Your credentials are not correct');
    }

    if ($request->isMethod('GET')) {
        return view('public.account.shipper', compact('title'));
    }
}



}
