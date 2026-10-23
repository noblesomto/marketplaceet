<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Advert;
use App\Models\Bank;
use App\Models\UserVerification;
use App\Mail\VerificationRequestMail;
use App\Rules\EthiopianPhoneNumber;
use Carbon\Carbon;
use App\Support\ActivityLog;

/**
 * @group User Profile
 *
 * APIs for managing user profile, settings, verification, and account preferences
 */
class UserProfile extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user/profile",
     *     summary="Get user profile",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User profile data")
     * )
     */
    public function getProfile(Request $request)
    {
        $user = auth()->user();
        $count_ads = Advert::where('user_id', $user->user_id)->count();

        $ads = Advert::with('firstImage')
            ->orderBy('created_at', 'desc')
            ->where('user_id', $user->user_id)
            ->paginate($request->get('per_page', 10));

        // Get profile image URL
        $profileImageUrl = $user->getFirstMediaUrl('profile_image', 'optimized')
            ?: $user->getFirstMediaUrl('profile_image');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => array_merge($user->toArray(), [
                    'profile_image_url' => $profileImageUrl,
                    'profile_thumbnail_url' => $user->getFirstMediaUrl('profile_image', 'thumbnail')
                ]),
                'ads_count' => $count_ads,
                'ads' => $ads->items(),
                'pagination' => [
                    'current_page' => $ads->currentPage(),
                    'last_page' => $ads->lastPage(),
                    'per_page' => $ads->perPage(),
                    'total' => $ads->total(),
                    'has_more' => $ads->hasMorePages()
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/about-account",
     *     summary="Get account information",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Account information")
     * )
     */
    public function aboutAccount(Request $request)
    {
        $user = auth()->user();
        $count_ads = Advert::where('user_id', $user->user_id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'ads_count' => $count_ads,
                'account_created' => $user->created_at,
                'last_login' => $user->updated_at,
                'verification_status' => $user->verified,
                'email_verified' => $user->email_verified_at !== null
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/ads",
     *     summary="Get user ads with pagination",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User ads")
     * )
     */
    public function loadMoreUserAds(Request $request)
    {
        $user = auth()->user();

        $ads = Advert::with('firstImage')
            ->orderBy('created_at', 'desc')
            ->where('user_id', $user->user_id)
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'per_page' => $ads->perPage(),
                'total' => $ads->total(),
                'has_more' => $ads->hasMorePages()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/settings",
     *     summary="Get user settings",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User settings")
     * )
     */
    public function getSettings()
    {
        $user = auth()->user();
        $count_ads = Advert::where('user_id', $user->user_id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'ads_count' => $count_ads,
                'notification_preferences' => $user->notification
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/profile-info",
     *     summary="Get profile information",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Profile information")
     * )
     */
    public function getProfileInfo()
    {
        $user = auth()->user();
        $count_ads = Advert::where('user_id', $user->user_id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'ads_count' => $count_ads
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/profile/address",
     *     summary="Update profile address and image",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="city", type="string"),
     *                 @OA\Property(property="state", type="string"),
     *                 @OA\Property(property="profile_image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated")
     * )
     */
    public function updateAddress(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:12048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'address', 'city', 'state']);
        $before = $user->only(array_keys($updateData));

        try {
            if ($request->hasFile('profile_image')) {
                $fileName = now()->format('YmdHis') . '_profile.' . $request->file('profile_image')->getClientOriginalExtension();

                $user->clearMediaCollection('profile_image');
                $user->addMediaFromRequest('profile_image')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_image');

                $media = $user->getFirstMedia('profile_image');

                // Delete original after conversions
                if ($media && $media->hasGeneratedConversion('optimized') && $media->hasGeneratedConversion('thumbnail')) {
                    $originalPath = $media->getPath();
                    if (file_exists($originalPath)) {
                        unlink($originalPath);
                    }
                }
            }

            $user->update($updateData);

            ActivityLog::record('profile', 'Updated profile', $user, $user, [
                'changed' => array_diff_assoc($updateData, $before),
                'source' => 'app',
            ]);

            // Get updated profile image URLs
            $profileImageUrl = $user->getFirstMediaUrl('profile_image', 'optimized')
                ?: $user->getFirstMediaUrl('profile_image');

            return response()->json([
                'success' => true,
                'message' => 'Profile information updated successfully',
                'data' => array_merge($user->fresh()->toArray(), [
                    'profile_image_url' => $profileImageUrl,
                    'profile_thumbnail_url' => $user->getFirstMediaUrl('profile_image', 'thumbnail')
                ])
            ]);

        } catch (\Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/user/profile/phone",
     *     summary="Update phone number",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Phone updated")
     * )
     */
    public function updatePhone(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'phone' => [
                'required',
                new EthiopianPhoneNumber(),
                Rule::unique('users', 'phone')->ignore($user->user_id, 'user_id'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $oldPhone = $user->phone;
        $user->update(['phone' => $request->phone]);

        ActivityLog::record('profile', 'Updated phone number', $user, $user, [
            'changed' => ['phone' => [$oldPhone, $request->phone]],
            'source' => 'app',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Phone number updated successfully',
            'data' => $user->fresh()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/verification",
     *     summary="Get verification status",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Verification status")
     * )
     */
    public function getVerificationStatus()
    {
        $user = auth()->user()->load('verification');
        $count_ads = Advert::where('user_id', $user->user_id)->count();

        $verificationDocUrl = null;
        $addressProofUrl = null;

        if ($user->verification) {
            $verificationDocUrl = $user->verification->getFirstMediaUrl('verification_documents');
            $addressProofUrl = $user->verification->getFirstMediaUrl('verification_address');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'ads_count' => $count_ads,
                'verification' => $user->verification,
                'verification_doc_url' => $verificationDocUrl,
                'address_proof_url' => $addressProofUrl
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/verification",
     *     summary="Submit verification documents",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"document_number", "document_file"},
     *                 @OA\Property(property="document_number", type="string"),
     *                 @OA\Property(property="document_type", type="string"),
     *                 @OA\Property(property="document_file", type="string", format="binary"),
     *                 @OA\Property(property="proof_address", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Verification submitted")
     * )
     */
    public function submitVerification(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'document_number' => 'required|string|max:255',
            'document_type' => 'nullable|string|max:255',
            'document_file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:12048',
            'proof_address' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:12048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $verification = UserVerification::updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'document_number' => $request->document_number,
                'document_type' => $request->document_type,
            ]
        );

        try {
            if ($request->hasFile('document_file')) {
                $fileName = now()->format('YmdHis') . '_document.' . $request->file('document_file')->getClientOriginalExtension();

                $verification->addMediaFromRequest('document_file')
                    ->usingFileName($fileName)
                    ->toMediaCollection('verification_documents');
            }

            if ($request->hasFile('proof_address')) {
                $fileName = now()->format('YmdHis') . '_address.' . $request->file('proof_address')->getClientOriginalExtension();

                $verification->addMediaFromRequest('proof_address')
                    ->usingFileName($fileName)
                    ->toMediaCollection('verification_address');
            }

            // Send email notification
            try {
                \Mail::to(config('global.site_email'))
                    ->queue(new VerificationRequestMail([
                        'user_id' => $user->user_id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'document_number' => $request->document_number,
                    ]));
            } catch (\Exception $e) {
                \Log::error('Verification email failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Verification information submitted successfully',
                'data' => $verification
            ]);

        } catch (\Exception $e) {
            \Log::error('Verification submission failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Verification submission failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/user/payment-info",
     *     summary="Get payment information",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Payment information")
     * )
     */
    public function getPaymentInfo()
    {
        $user = auth()->user();
        $count_ads = Advert::where('user_id', $user->user_id)->count();
        $banks = Bank::all();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'ads_count' => $count_ads,
                'banks' => $banks,
                'payment_info' => [
                    'bank_name' => $user->bank_name,
                    'bank_code' => $user->bank_code,
                    'account_name' => $user->account_name,
                    'account_number' => $user->account_number,
                ]
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/payment-info",
     *     summary="Update payment information",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bank_name", "bank_code", "account_number", "account_name"},
     *             @OA\Property(property="bank_name", type="string"),
     *             @OA\Property(property="bank_code", type="string"),
     *             @OA\Property(property="account_number", type="string"),
     *             @OA\Property(property="account_name", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment info updated")
     * )
     */
    public function updatePaymentInfo(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string',
            'bank_code' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'bank_name' => $request->bank_name,
            'bank_code' => $request->bank_code,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment information updated successfully',
            'data' => $user->fresh()
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/password",
     *     summary="Change password",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"old_password", "password", "password_confirmation"},
     *             @OA\Property(property="old_password", type="string", format="password"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password changed")
     * )
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The current password does not match'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/notifications",
     *     summary="Update notification preferences",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"notifications"},
     *             @OA\Property(property="notifications", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Notifications updated")
     * )
     */
    public function updateNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notifications' => 'required|string|in:yes,no,enabled,disabled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $map = ['enabled' => 'yes', 'disabled' => 'no', 'yes' => 'yes', 'no' => 'no'];
        $value = $map[$request->notifications] ?? 'no';

        $user = auth()->user();
        $user->notification = $value;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated',
            'data' => $user
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/user/account",
     *     summary="Disable account",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Account disabled")
     * )
     */
    public function disableAccount(Request $request)
    {
        $user = auth()->user();

        $user->update([
            'disable_account' => "yes",
            'disable_account_date' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Revoke all tokens
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deactivated successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/logout",
     *     summary="Logout user",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Logged out")
     * )
     */
    public function logout(Request $request)
    {
        $user = auth()->user();

        // Clear remember token
        $user->update(['remember_token' => null]);

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
