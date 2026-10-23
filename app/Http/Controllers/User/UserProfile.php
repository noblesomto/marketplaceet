<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UpdatePaymentInfoRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\SubmitVerificationRequest;
use App\Models\User;
use App\Models\Advert;
use App\Models\Bank;
use App\Models\State;
use App\Models\UserVerification;
use App\Helpers\FileUploadHelper;
use Carbon\Carbon;
use Hash;
use Mail;
use App\Mail\VerificationRequestMail;
use App\Rules\EthiopianPhoneNumber;
use App\Services\ImageProcessingService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Traits\HasUserSession;
use App\Support\ActivityLog;


class UserProfile extends Controller
{
    use HasUserSession;
    public function profile(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $ads = Advert::with('media')->orderBy('created_at', 'desc')->where('user_id', $user_id)->paginate(10);
        $hasMore = $ads->hasMorePages();

        return view('user.settings.profile', compact('title','user','count_ads','ads','hasMore'));
    }

    public function profileUpdate(UpdateProfileRequest $request)
    {
        $title = "Update Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->firstOrFail();
        $count_ads = Advert::where('user_id', $user_id)->count();

        if ($request->isMethod('GET')) {
            return view('user.settings.profile-update', compact('title', 'user', 'count_ads'));
        }

        $changes = array_filter([
            'name'  => $request->input('name') !== $user->name ? [$user->name, $request->input('name')] : null,
            'phone' => $request->input('phone') !== $user->phone ? [$user->phone, $request->input('phone')] : null,
        ]);

        DB::table('users')->where('user_id', $user_id)->update([
            'name'  => $request->input('name'),
            'phone' => $request->input('phone'),
        ]);

        if (!empty($changes)) {
            ActivityLog::record('profile', 'Updated profile', $user, $user, ['changed' => $changes]);
        }

        $request->session()->forget('must_complete_phone');

        if ($request->hasFile('profile_image')) {
            $user->clearMediaCollection('profile_image');
            $user->addMediaFromRequest('profile_image')
                ->toMediaCollection('profile_image');
        }

        // If the user was redirected here by RequireProfileComplete, send them back
        $intended = $request->session()->pull('url.intended');

        return redirect($intended ?? route('user.index'))
            ->with('success', 'Profile updated successfully!');
    }

    public function about_account(Request $request)
    {
        $title = "About Account | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $ads = Advert::with('media')->orderBy('created_at', 'desc')->where('user_id', $user_id)->paginate(10);
        $hasMore = $ads->hasMorePages();

        return view('user.settings.about-account', compact('title','user','count_ads','ads','hasMore'));

    }

    public function loadMoreUserAds(Request $request)
    {
        $user_id = $request->session()->get('user_id');

        if (!$user_id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        $ads = Advert::with('media')
                    ->orderBy('created_at', 'desc')
                    ->where('user_id', $user_id)
                    ->paginate(10);

        return response()->json([
            'html' => view('user.components.my-ads', ['ads' => $ads])->render(),
            'hasMore' => $ads->hasMorePages()
        ]);
    }

    public function settings(Request $request)
    {
        $title = "Seetings | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        return view('user.settings.settings', compact('title','user','count_ads'));

    }


    public function profile_address(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->firstOrFail();
        $count_ads = Advert::where('user_id', $user_id)->count();

        if ($request->isMethod('GET')) {
            $states = State::orderBy('name')->get();
            return view('user.settings.profile-address', compact('title','user','count_ads','states'));
        }

        if ($request->isMethod('POST')) {
            $request->validate([
                'name'          => 'required',
                'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:12048',
            ]);

            $updateData = $request->only(['name', 'address', 'city', 'state']);

            try {
                if ($request->hasFile('profile_image')) {
                    $fileName = now()->format('YmdHis') . '_profile.' . $request->file('profile_image')->getClientOriginalExtension();

                    $user->clearMediaCollection('profile_image');
                    $user->addMediaFromRequest('profile_image')
                        ->usingFileName($fileName)
                        ->toMediaCollection('profile_image');

                    $media = $user->getFirstMedia('profile_image');

                    // after conversions finish inline, delete original
                    if ($media && $media->hasGeneratedConversion('optimized') && $media->hasGeneratedConversion('thumbnail')) {
                            $originalPath = $media->getPath();
                            if (file_exists($originalPath)) {
                                unlink($originalPath);
                            }
                        }

                }
            } catch (\Exception $e) {
                \Log::error('Profile image upload failed: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Profile image upload failed. Please try again.');
            }

            $before = $user->only(array_keys($updateData));
            $user->update($updateData);

            ActivityLog::record('profile', 'Updated profile', $user, $user, [
                'changed' => array_diff_assoc($updateData, $before),
            ]);

            return redirect()->back()->with('success', 'Profile information updated successfully!');
        }
    }


    public function profile_info(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        return view('user.settings.profile-info', compact('title','user','count_ads'));
    }

    public function profile_phone(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        $request->validate([
            'phone' => [
                'required',
                new EthiopianPhoneNumber(),
                Rule::unique('users', 'phone')->ignore($user_id, 'user_id'),
            ],
        ]);

        $oldPhone = $user->phone;

        DB::table('users')
            ->where('user_id', $user_id)
            ->update([
                'phone' => $request->input('phone'),
            ]);

        ActivityLog::record('profile', 'Updated phone number', $user, $user, [
            'changed' => ['phone' => [$oldPhone, $request->input('phone')]],
        ]);

        return redirect()->back()->with('success', 'Profile Information updated successfully!');
    }


    public function get_verified(Request $request)
    {
        $title = "Get Verified | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::with('verification')->where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        return view('user.settings.get-verified', compact('title','user','count_ads'));
    }



    public function submit_verification(SubmitVerificationRequest $request)
    {
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->firstOrFail();

        $verification = UserVerification::updateOrCreate(
            ['user_id' => $user_id],
            [
                'document_number' => $request->document_number,
                'document_type'   => $request->document_type,
            ]
        );


        try {
            // Handle document file upload
            if ($request->hasFile('document_file')) {
                $fileName = now()->format('YmdHis') . '_document.' . $request->file('document_file')->getClientOriginalExtension();

                $verification->addMediaFromRequest('document_file')
                    ->usingFileName($fileName)
                    ->toMediaCollection('verification_documents');
            }

            // Handle proof of address upload (optional)
            if ($request->hasFile('proof_address')) {
                $fileName = now()->format('YmdHis') . '_address.' . $request->file('proof_address')->getClientOriginalExtension();

                $verification->addMediaFromRequest('proof_address')
                    ->usingFileName($fileName)
                    ->toMediaCollection('verification_address');
            }

        } catch (\Exception $e) {
            \Log::error('File upload failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'File upload failed. Please try again.');
        }

        // Send email notification
        try {
            \Mail::to(config('global.site_email'))
                ->queue(new VerificationRequestMail([
                    'user_id'         => $user->user_id,
                    'name'            => $user->name,
                    'email'           => $user->email,
                    'document_number' => $request->document_number,
                ]));
        } catch (\Exception $e) {
            \Log::error('Verification email failed: ' . $e->getMessage());
        }

        ActivityLog::record('profile', 'Submitted identity verification documents', $user, $user, [
            'document_type' => $request->document_type,
        ]);

        return redirect()->back()->with('success', 'Verification information submitted successfully!');
    }



    public function payment_info(UpdatePaymentInfoRequest $request)
    {
        $title = "Payment Information | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $banks = Bank::all();
        if ($request->isMethod('GET')) {
            return view('user.settings.payment-info', compact('title','user','count_ads','banks'));
        }

         if ($request->isMethod('POST')) {


            $user = DB::table('users')
                ->where('user_id', $user_id)
                ->update([
                    'bank_name'=> $request->input('bank_name'),
                    'bank_code'=> $request->input('bank_code'),
                    'account_name'=> $request->input('account_name'),
                    'account_number'=> $request->input('account_number'),
                ]);

            $userModel = User::where('user_id', $user_id)->first();
            ActivityLog::record('profile', 'Updated bank/payout details', $userModel, $userModel, [
                'bank_name' => $request->input('bank_name'),
                'account_number_last4' => substr((string) $request->input('account_number'), -4),
            ]);

             return redirect()->back()->with('success', 'Payment Information updated successfully!');
        }
    }

    public function change_password(ChangePasswordRequest $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        $password = $request->old_password;

        if (Hash::check($password, $user->password)) {

            DB::table('users')
                ->where('user_id', $user_id)
                ->update([
                    'password'=> Hash::make($request->input('password')),
                ]);

            ActivityLog::record('auth', 'Changed password', $user, $user);

            return redirect()->back()->with('success', 'Password Changed successfully!');
        }else{

            return redirect()->back()->with('error', 'The Current Password Does not match!');
        }

    }

    public function disable_account(Request $request)
{
    $title = "My Profile | " . config('global.site_name');
    $user_id = $request->session()->get('user_id');
    $user = User::where('user_id', $user_id)->first();
    $count_ads = Advert::where('user_id', $user_id)->count();

    // Update user account status
    DB::table('users')
        ->where('user_id', $user_id)
        ->update([
            'disable_account'=> "yes",
            'disable_account_date'=> Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

    ActivityLog::record('auth', 'Deactivated own account', $user, $user);

    // Invalidate the entire session (this clears all session data)
    $request->session()->invalidate();

    // Regenerate CSRF token to prevent reuse
    $request->session()->regenerateToken();

    return redirect("login")
        ->with('success', 'Account Deactivated successfully!')
        ->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
}

    public function profile_notification(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        return view('user.settings.profile-notification', compact('title','user','count_ads'));
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'notifications' => 'required'
        ]);

        //dd($validated['notifications']);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $user->notification = $validated['notifications'];
        $user->save();

        return response()->json([
            'success' => true,
         ]);
    }

    public function logout(Request $request)
{
    $user_id = $request->session()->get('user_id');

    if ($user_id) {
        $user = User::where('user_id', $user_id)->first();

        if ($user) {
            // Clear remember token
            $user->update(['remember_token' => null]);

            ActivityLog::record('auth', 'Logged out', $user, $user);
        }
    }

    $request->session()->flush();
    cookie()->queue(cookie()->forget('remember_login'));
    cookie()->queue(cookie()->forget('trusted_device'));

    return redirect("login")->with('success', 'Logged out successfully!');
}
}
