<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Advert;
use App\Models\AdvertBoost;
use Carbon\Carbon;

/**
 * @group Boosts
 *
 * APIs for managing advert boosts and boost packages
 */
class UserManageBoostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user/boosts",
     *     summary="Get user's boost history",
     *     tags={"Boosts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="User boost history")
     * )
     */
    public function getUserBoosts(Request $request)
    {
        $user = auth()->user();

        $boosts = AdvertBoost::with(['advert.firstImage'])
            ->where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $boosts->items(),
            'pagination' => [
                'current_page' => $boosts->currentPage(),
                'last_page' => $boosts->lastPage(),
                'per_page' => $boosts->perPage(),
                'total' => $boosts->total()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/{advertId}/boost-info",
     *     summary="Get boost information for an advert",
     *     tags={"Boosts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="advertId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Boost information")
     * )
     */
    public function getBoostInfo($advertId)
    {
        $user = auth()->user();

        $advert = Advert::with(['images', 'car', 'phone', 'boost'])
            ->where('id', $advertId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found or unauthorized'
            ], 404);
        }

        $price = 500; // Boost price

        return response()->json([
            'success' => true,
            'data' => [
                'advert' => $advert,
                'boost_price' => $price,
                //'has_active_boost' => $advert->boost && $advert->boost->status === 'active',
                'current_boost' => $advert->boost
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/adverts/{advertId}/boost",
     *     summary="Create a boost request for an advert",
     *     tags={"Boosts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="advertId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="duration_days", type="integer", description="Boost duration in days", example=7)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Boost request created")
     * )
     */
    public function createBoost(Request $request, $advertId)
    {
        $validator = Validator::make($request->all(), [
            'duration_days' => 'nullable|integer|min:1|max:30'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        $advert = Advert::where('id', $advertId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found or unauthorized'
            ], 404);
        }

        // Check if there's already an active boost
        $existingBoost = AdvertBoost::where('advert_id', $advertId)
            ->where('boost_status', 'active')
            ->where('payment_status', 'paid')
            ->first();

        if ($existingBoost) {
            return response()->json([
                'success' => false,
                'message' => 'This advert already has an active boost'
            ], 400);
        }

        $durationDays = $request->input('duration_days', 7);
        $boost = AdvertBoost::create([
            'user_id' => $user->user_id,
            'advert_id' => $advertId,
            'payment_reference' => 'boost-' . round(microtime(true) * 1000),
            'boost_status' => 'pending',
            'payment_status' => 'pending',
            'duration' => $durationDays,
            'amount' => 500, // Base price
            'upload_proof' => 'no'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Boost request created successfully',
            'data' => $boost
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/boosts/{boostId}",
     *     summary="Get boost details",
     *     tags={"Boosts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="boostId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Boost details")
     * )
     */
    public function getBoost($boostId)
    {
        $user = auth()->user();

        $boost = AdvertBoost::with(['advert.firstImage', 'user'])
            ->where('id', $boostId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$boost) {
            return response()->json([
                'success' => false,
                'message' => 'Boost not found or unauthorized'
            ], 404);
        }

        // Get payment proof media if exists
        $paymentProof = $boost->getFirstMediaUrl('payment_proof');

        return response()->json([
            'success' => true,
            'data' => [
                'boost' => $boost,
                'payment_proof_url' => $paymentProof ?: null,
                'price' => 500
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/boosts/{boostId}/upload-proof",
     *     summary="Upload payment proof for boost",
     *     tags={"Boosts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="boostId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"payment_proof"},
     *                 @OA\Property(property="payment_proof", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment proof uploaded")
     * )
     */
    public function uploadProof(Request $request, $boostId)
    {
        $validator = Validator::make($request->all(), [
            'payment_proof' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:12048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        $advertBoost = AdvertBoost::where('id', $boostId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advertBoost) {
            return response()->json([
                'success' => false,
                'message' => 'Boost not found or unauthorized'
            ], 404);
        }

        try {
            // Add the payment proof to media library
            $advertBoost->addMediaFromRequest('payment_proof')
                ->toMediaCollection('payment_proof');

            // Update the upload_proof field
            $advertBoost->update([
                'upload_proof' => 'yes',
                'updated_at' => Carbon::now(),
            ]);

            $paymentProofUrl = $advertBoost->getFirstMediaUrl('payment_proof');

            return response()->json([
                'success' => true,
                'message' => 'Payment proof uploaded successfully',
                'data' => [
                    'boost' => $advertBoost,
                    'payment_proof_url' => $paymentProofUrl
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload payment proof',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/boosts/{boostId}",
     *     summary="Cancel a boost request",
     *     tags={"Boosts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="boostId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Boost cancelled")
     * )
     */
    public function cancelBoost($boostId)
    {
        $user = auth()->user();

        $boost = AdvertBoost::where('id', $boostId)
            ->where('user_id', $user->user_id)
            ->where('boost_status', 'pending')
            ->where('payment_status', 'pending')
            ->first();

        if (!$boost) {
            return response()->json([
                'success' => false,
                'message' => 'Boost not found, unauthorized, or cannot be cancelled'
            ], 404);
        }

        $boost->update(['boost_status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Boost request cancelled successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/{advertId}/boost-status",
     *     summary="Check boost status for an advert",
     *     tags={"Boosts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="advertId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Boost status")
     * )
     */
    public function checkBoostStatus($advertId)
    {
        $user = auth()->user();

        $advert = Advert::where('id', $advertId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found or unauthorized'
            ], 404);
        }

        $activeBoost = AdvertBoost::where('advert_id', $advertId)
            ->where('boost_status', 'active')
            ->where('payment_status', 'paid')
            ->first();

        $pendingBoost = AdvertBoost::where('advert_id', $advertId)
            ->where('boost_status', 'pending')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'has_active_boost' => !is_null($activeBoost),
                'has_pending_boost' => !is_null($pendingBoost),
                'active_boost' => $activeBoost,
                'pending_boost' => $pendingBoost,
                'can_create_boost' => is_null($activeBoost) && is_null($pendingBoost)
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/boosts/active",
     *     summary="Get all active boosts for user",
     *     tags={"Boosts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Active boosts")
     * )
     */
    public function getActiveBoosts(Request $request)
    {
        $user = auth()->user();

        $boosts = AdvertBoost::with(['advert.firstImage'])
            ->where('user_id', $user->user_id)
            ->where('boost_status', 'active')
            ->where('payment_status', 'paid')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $boosts->items(),
            'pagination' => [
                'current_page' => $boosts->currentPage(),
                'last_page' => $boosts->lastPage(),
                'per_page' => $boosts->perPage(),
                'total' => $boosts->total()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/boosts/pending",
     *     summary="Get all pending boosts for user",
     *     tags={"Boosts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Pending boosts")
     * )
     */
    public function getPendingBoosts(Request $request)
    {
        $user = auth()->user();

        $boosts = AdvertBoost::with(['advert.firstImage'])
            ->where('user_id', $user->user_id)
            ->where('boost_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $boosts->items(),
            'pagination' => [
                'current_page' => $boosts->currentPage(),
                'last_page' => $boosts->lastPage(),
                'per_page' => $boosts->perPage(),
                'total' => $boosts->total()
            ]
        ]);
    }
}
