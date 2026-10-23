<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeviceTokenController extends Controller
{
    /**
     * Register device token
     *
     * Register or update a device token for push notifications (FCM).
     * If the token already exists, it will be updated with new user and platform information.
     *
     * @group Notifications
     * @authenticated
     *
     * @bodyParam token string required FCM device token. Example: cDNfP4RjSluKPQQ...
     * @bodyParam platform string required Device platform (android or ios). Example: android
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Device token registered successfully",
     *   "data": {
     *     "id": 1,
     *     "platform": "android"
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        // Debug log — fires before validation so we catch every attempt including malformed payloads
        Log::info('DeviceToken: registration attempt', [
            'user_id'    => $request->user()?->id,
            'platform'   => $request->input('platform'),
            'token_prefix' => substr($request->input('token', ''), 0, 20) ?: '(empty)',
            'user_agent' => $request->userAgent(),
            'ip'         => $request->ip(),
        ]);

        try {
            $validated = $request->validate([
                'token'    => 'required|string|max:255',
                'platform' => ['required', Rule::in(['android', 'ios'])],
            ]);
        } catch (ValidationException $e) {
            Log::warning('DeviceToken: validation failed', [
                'user_id' => $request->user()?->id,
                'payload' => $request->only('platform', 'token'),
                'errors'  => $e->errors(),
            ]);
            throw $e;
        }

        $deviceToken = DeviceToken::updateOrCreate(
            [
                'token' => $validated['token'],
            ],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'],
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        Log::info('DeviceToken: registered successfully', [
            'user_id'  => $request->user()->id,
            'platform' => $validated['platform'],
            'token_id' => $deviceToken->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device token registered successfully',
            'data' => [
                'id' => $deviceToken->id,
                'platform' => $deviceToken->platform,
            ]
        ], 201);
    }

    /**
     * Get device tokens
     *
     * Retrieve all device tokens registered for the authenticated user.
     *
     * @group Notifications
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "platform": "android",
     *       "is_active": true,
     *       "last_used_at": "2026-01-19T10:30:00.000000Z",
     *       "created_at": "2026-01-15T08:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()
            ->deviceTokens()
            ->select('id', 'platform', 'is_active', 'last_used_at', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tokens,
        ]);
    }

    /**
     * Delete device token
     *
     * Remove a device token to stop receiving push notifications on that device.
     *
     * @group Notifications
     * @authenticated
     *
     * @urlParam id integer required Device token ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Device token removed successfully"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Device token not found"
     * }
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $deleted = DeviceToken::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Device token not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device token removed successfully',
        ]);
    }
}
