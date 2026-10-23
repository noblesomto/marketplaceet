<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BlockedUser;

/**
 * @group Users
 *
 * APIs for blocking and unblocking users
 */
class BlockUserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/users/block",
     *     summary="Block a user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"blocked_id"},
     *             @OA\Property(property="blocked_id", type="string", description="User ID to block"),
     *             @OA\Property(property="advert_id", type="integer", nullable=true, description="Optional advert ID context")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User blocked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Cannot block yourself"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function block(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'blocked_id' => 'required|exists:users,user_id',
            'advert_id' => 'nullable|exists:adverts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        if ($user->user_id == $request->blocked_id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot block yourself.'
            ], 400);
        }

        $blocked = BlockedUser::firstOrCreate([
            'blocker_id' => $user->user_id,
            'blocked_id' => $request->blocked_id,
            'advert_id' => $request->advert_id ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully.',
            'data' => $blocked
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/users/unblock",
     *     summary="Unblock a user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"blocked_id"},
     *             @OA\Property(property="blocked_id", type="string", description="User ID to unblock"),
     *             @OA\Property(property="advert_id", type="integer", nullable=true, description="Optional advert ID context")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User unblocked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Block record not found")
     * )
     */
    public function unblock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'blocked_id' => 'required|exists:users,user_id',
            'advert_id' => 'nullable|exists:adverts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        $deleted = BlockedUser::where('blocker_id', $user->user_id)
            ->where('blocked_id', $request->blocked_id)
            ->where('advert_id', $request->advert_id ?? null)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'User unblocked successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Block record not found.'
        ], 404);
    }

    /**
     * @OA\Get(
     *     path="/api/users/blocked",
     *     summary="Get list of blocked users",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Results per page",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of blocked users",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getBlockedUsers(Request $request)
    {
        $user = auth()->user();

        $blocked = BlockedUser::where('blocker_id', $user->user_id)
            ->with(['blockedUser', 'advert'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $blocked->items(),
            'pagination' => [
                'current_page' => $blocked->currentPage(),
                'last_page' => $blocked->lastPage(),
                'per_page' => $blocked->perPage(),
                'total' => $blocked->total()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/check-blocked/{userId}",
     *     summary="Check if a user is blocked",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID to check",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="advert_id",
     *         in="query",
     *         description="Optional advert ID context",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Block status",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="is_blocked", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function checkBlocked($userId, Request $request)
    {
        $user = auth()->user();

        $isBlocked = BlockedUser::where('blocker_id', $user->user_id)
            ->where('blocked_id', $userId)
            ->when($request->has('advert_id'), function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('advert_id', $request->advert_id)
                      ->orWhereNull('advert_id');
                });
            })
            ->exists();

        return response()->json([
            'success' => true,
            'is_blocked' => $isBlocked
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/users/block-all/{userId}",
     *     summary="Block user globally (all contexts)",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID to block globally",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User blocked globally",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Cannot block yourself"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function blockGlobally($userId)
    {
        $user = auth()->user();

        if ($user->user_id == $userId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot block yourself.'
            ], 400);
        }

        // Create global block (advert_id = null means all contexts)
        $blocked = BlockedUser::firstOrCreate([
            'blocker_id' => $user->user_id,
            'blocked_id' => $userId,
            'advert_id' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User blocked globally.',
            'data' => $blocked
        ]);
    }
}
