<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    /**
     * Get notification settings
     *
     * Retrieve the current notification settings for the authenticated user.
     *
     * @group Notifications
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "push_notifications_enabled": true
     *   }
     * }
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'push_notifications_enabled' => $request->user()->push_notifications_enabled,
            ],
        ]);
    }

    /**
     * Update notification settings
     *
     * Update push notification preferences for the authenticated user.
     *
     * @group Notifications
     * @authenticated
     *
     * @bodyParam push_notifications_enabled boolean required Enable or disable push notifications. Example: true
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Notification settings updated successfully",
     *   "data": {
     *     "push_notifications_enabled": true
     *   }
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'push_notifications_enabled' => 'required|boolean',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully',
            'data' => [
                'push_notifications_enabled' => $request->user()->push_notifications_enabled,
            ],
        ]);
    }
}
