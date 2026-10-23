<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * @group User Statistics
 *
 * APIs for retrieving user statistics, feedback, and notifications
 */
class UserStatsController extends Controller
{
    /**
     * Get authenticated user statistics
     *
     * Returns comprehensive statistics for the currently authenticated user including
     * followers count, unread messages, notifications, and feedback ratings.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "followers_count": 150,
     *     "unread_messages_count": 5,
     *     "unread_notifications_count": 3,
     *     "feedback": {
     *       "rating": 4.5,
     *       "satisfaction": 4.2,
     *       "reliable": 4.8,
     *       "friendly": 4.3,
     *       "count": 20
     *     },
     *     "feedback_labels": {
     *       "satisfaction": {
     *         "label": "Very",
     *         "color": "bg-blue-100 text-blue-800"
     *       },
     *       "friendly": {
     *         "label": "Very",
     *         "color": "bg-blue-100 text-blue-800"
     *       },
     *       "reliable": {
     *         "label": "Very",
     *         "color": "bg-blue-100 text-blue-800"
     *       }
     *     }
     *   }
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "message": "User not authenticated"
     * }
     */
    // GET /api/user/stats
    public function getUserStats(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $userCode = User::where('id', $user->id)->value('user_id');

            $stats = [
                'followers_count' => countUserFollowers($userCode),
                'unread_messages_count' => getTotalUnreadMessages(),
                'unread_notifications_count' => getUserNotificationCount(),
                'feedback' => get_user_feedback_averages($userCode),
                'feedback_labels' => feedback_rating_labels($userCode)
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user followers count
     *
     * Retrieves the total number of followers for a specific user.
     *
     * @authenticated
     *
     * @urlParam userId string required The user ID (5-character code). Example: ABC12
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user_id": "ABC12",
     *     "followers_count": 150
     *   }
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to fetch followers count",
     *   "error": "Error details"
     * }
     */
    // GET /api/user/{userId}/followers
    public function getUserFollowers($userId)
    {
        try {
            $count = countUserFollowers($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'followers_count' => $count
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch followers count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user feedback and ratings
     *
     * Retrieves feedback averages and rating labels for a specific user.
     * Includes overall rating, satisfaction, reliability, and friendliness scores.
     *
     * @authenticated
     *
     * @urlParam userId string required The user ID (5-character code). Example: ABC12
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user_id": "ABC12",
     *     "averages": {
     *       "rating": 4.5,
     *       "satisfaction": 4.2,
     *       "reliable": 4.8,
     *       "friendly": 4.3,
     *       "count": 20
     *     },
     *     "labels": {
     *       "satisfaction": {
     *         "label": "Very",
     *         "color": "bg-blue-100 text-blue-800"
     *       },
     *       "friendly": {
     *         "label": "Very",
     *         "color": "bg-blue-100 text-blue-800"
     *       },
     *       "reliable": {
     *         "label": "Very",
     *         "color": "bg-blue-100 text-blue-800"
     *       }
     *     }
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "User not found"
     * }
     */
    // GET /api/user/{userId}/feedback
    public function getUserFeedback($userId)
    {
        try {
            $user = User::where('user_id', $userId)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $averages = get_user_feedback_averages($userId);
            $labels = feedback_rating_labels($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'averages' => $averages,
                    'labels' => $labels
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread messages count
     *
     * Returns the count of unread messages for the authenticated user.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "unread_count": 5
     *   }
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "message": "User not authenticated"
     * }
     */
    // GET /api/user/unread-messages
    public function getUnreadMessagesCount()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $count = getTotalUnreadMessages();

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $count
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch unread messages count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET /api/user/unread-notifications
    public function getUnreadNotificationsCount()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $count = getUserNotificationCount();

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $count
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch unread notifications count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user notifications
     *
     * Retrieves notifications for the authenticated user with pagination support.
     * Returns both the notifications list and unread count.
     *
     * @authenticated
     *
     * @queryParam limit integer The number of notifications to return. Defaults to 10. Example: 20
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "notifications": [
     *       {
     *         "id": 1,
     *         "type": "message",
     *         "message": "You have a new message",
     *         "is_read": false,
     *         "created_at": "2025-01-09T10:30:00.000000Z",
     *         "seller": {
     *           "user_id": "DEF45",
     *           "name": "John Doe"
     *         },
     *         "advert": {
     *           "id": 123,
     *           "title": "Samsung Galaxy S21"
     *         }
     *       }
     *     ],
     *     "unread_count": 3
     *   }
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "message": "User not authenticated"
     * }
     */
    // GET /api/user/notifications
    public function getNotifications(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $limit = $request->input('limit', 10);
            $notifications = getUserNotifications($limit);
            $unreadCount = getUserNotificationCount();

            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => $notifications,
                    'unread_count' => $unreadCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
