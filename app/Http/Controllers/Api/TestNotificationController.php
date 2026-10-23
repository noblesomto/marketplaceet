<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseCloudMessagingService;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestNotificationController extends Controller
{
    /**
     * Send a test push notification
     *
     * This endpoint allows you to test push notifications locally.
     * Only available in local/development environment.
     *
     * @group Testing
     * @authenticated
     *
     * @bodyParam title string Notification title. Example: Hello from Marketplace
     * @bodyParam message string required Notification message. Example: This is a test notification
     * @bodyParam data array Additional data to send with notification. Example: {"test": true}
     * @bodyParam target_user_id integer Send to specific user's devices. Example: 1
     * @bodyParam device_token string Send to specific device token
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Test notification sent successfully",
     *   "data": {
     *     "tokens_count": 1,
     *     "notification": {
     *       "title": "Test Notification",
     *       "body": "This is a test message"
     *     }
     *   }
     * }
     */
    public function sendTest(Request $request, FirebaseCloudMessagingService $fcmService): JsonResponse
    {
        // Only allow in local/development environment
        if (!app()->environment(['local', 'development', 'testing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Test notifications are only available in development environment',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'message' => 'required|string|max:500',
            'data' => 'nullable|array',
            'target_user_id' => 'nullable|string|exists:users,user_id',
            'device_token' => 'nullable|string',
        ]);

        // Get device tokens
        $tokens = $this->getTargetTokens($request, $validated);

        if (empty($tokens)) {
            return response()->json([
                'success' => false,
                'message' => 'No active device tokens found',
                'suggestion' => 'Register a device token first using POST /api/device-tokens',
            ], 404);
        }

        // Prepare notification
        $notification = [
            'title' => $validated['title'] ?? 'Test from Marketplace',
            'body' => $validated['message'],
        ];

        $data = array_merge([
            'type' => 'test_notification',
            'timestamp' => now()->toIso8601String(),
            'test_id' => uniqid('test_'),
        ], $validated['data'] ?? []);

        // Send notification
        $result = $fcmService->sendToTokens($tokens, $notification, $data);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully',
                'data' => [
                    'tokens_count' => count($tokens),
                    'notification' => $notification,
                    'data' => $data,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send notification',
            'suggestion' => 'Check FCM_SERVER_KEY configuration and logs',
        ], 500);
    }

    /**
     * Get user's registered device tokens
     *
     * @group Testing
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user_tokens": [
     *       {
     *         "id": 1,
     *         "platform": "android",
     *         "token": "cDNfP...",
     *         "is_active": true,
     *         "last_used_at": "2026-01-13T10:30:00.000000Z"
     *       }
     *     ],
     *     "total_count": 1,
     *     "active_count": 1
     *   }
     * }
     */
    public function getMyTokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->deviceTokens()
            ->select('id', 'platform', 'token', 'is_active', 'last_used_at', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user_tokens' => $tokens,
                'total_count' => $tokens->count(),
                'active_count' => $tokens->where('is_active', true)->count(),
            ],
        ]);
    }

    /**
     * Get target device tokens based on request parameters
     */
    protected function getTargetTokens(Request $request, array $validated): array
    {
        // Specific device token
        if (!empty($validated['device_token'])) {
            return [$validated['device_token']];
        }

        // Specific user — device_tokens.user_id stores users.id (autoincrement), resolve first
        if (!empty($validated['target_user_id'])) {
            $targetUser = \App\Models\User::where('user_id', $validated['target_user_id'])->first();
            if (!$targetUser) {
                return [];
            }
            return DeviceToken::where('user_id', $targetUser->id)
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();
        }

        // Current user's tokens
        return $request->user()->deviceTokens()
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();
    }
}
