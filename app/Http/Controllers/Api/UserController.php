<?php

namespace App\Http\Controllers\Api;

use App\Services\BuyDirectEscrow;
use App\Support\BuyDirectStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\Advert;
use App\Models\Feedback;
use App\Models\Payment;
use App\Models\Wishlist;
use App\Models\Category;
use App\Models\Followers;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Support\ShippingStatusBadge;

/**
 * @group User
 *
 * APIs for user dashboard, ads, wishlist, payments, and user interactions
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user/dashboard",
     *     summary="Get user dashboard data",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="ads_count", type="integer"),
     *                 @OA\Property(property="recent_ads", ref="#/components/schemas/AdvertList")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function dashboard()
    {
        $user = auth()->user();

        $adsCount = Advert::where('user_id', $user->user_id)->count();
        $recentAds = Advert::with('firstImage')
            ->where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'ads_count' => $adsCount,
                'recent_ads' => $recentAds
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/categories",
     *     summary="Get all categories",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Categories list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Category"))
     *         )
     *     )
     * )
     */
    public function categories()
    {
        $categories = Category::orderBy('category', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/messages/conversations",
     *     summary="Get user conversations",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Conversations list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Conversation"))
     *         )
     *     )
     * )
     */
    public function conversations()
    {
        $user = auth()->user();
        $userId = $user->user_id;

        $conversations = Message::select(
                DB::raw("(CASE WHEN sender_id = {$userId} THEN receiver_id ELSE sender_id END) AS other_user_id"),
                'advert_id'
            )
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->distinct()
            ->get()
            ->map(function ($conversation) use ($userId) {
                $advert = Advert::find($conversation->advert_id);
                $otherUser = User::find($conversation->other_user_id);

                $unreadCount = Message::where('advert_id', $conversation->advert_id)
                    ->where('receiver_id', $userId)
                    ->where('sender_id', $conversation->other_user_id)
                    ->where('is_read', false)
                    ->count();

                return [
                    'advert' => $advert,
                    'advert_thumbnail_url' => $advert ? $advert->getFirstImageUrl('thumbnail') : null,
                    'other_user' => $otherUser,
                    'unread_count' => $unreadCount
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/ads",
     *     summary="Get user's ads",
     *     tags={"Ads"},
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
     *         description="Items per page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User's ads",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/AdvertList")
     *         )
     *     )
     * )
     */
    public function myAds(Request $request)
    {
        $user = auth()->user();

        $ads = Advert::with('firstImage')
            ->where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $ads
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/user/ads/{adId}/status",
     *     summary="Update ad status",
     *     tags={"Ads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="adId",
     *         in="path",
     *         required=true,
     *         description="Ad ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ad status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ad not found"
     *     )
     * )
     */
    public function updateAdStatus($adId, Request $request)
    {
        // Sellers may only toggle active<->disabled themselves. banned/pending_review
        // are admin/resubmit-only transitions — see resubmitAdvert().
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:active,disabled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $advert = Advert::where('id', $adId)
            ->where('user_id', $user->user_id)
            ->whereIn('ad_status', ['active', 'disabled'])
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found or not eligible for this status change'
            ], 404);
        }

        $advert->update(['ad_status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Advert status updated successfully'
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/ads/{adId}/resubmit",
     *     summary="Resubmit a banned ad for admin review",
     *     tags={"User Ads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="adId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advert resubmitted for review"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ad not found or not eligible for resubmission"
     *     )
     * )
     */
    public function resubmitAdvert($adId)
    {
        $user = auth()->user();
        $advert = Advert::where('id', $adId)
            ->where('user_id', $user->user_id)
            ->where('ad_status', 'banned')
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found or not eligible for resubmission'
            ], 404);
        }

        $advert->update([
            'ad_status' => 'pending_review',
            'resubmitted_at' => now(),
        ]);

        \App\Models\AdvertModerationLog::create([
            'advert_id' => $advert->id,
            'admin_id' => null,
            'action' => 'resubmitted',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Advert resubmitted for review'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/wishlist/{adId}",
     *     summary="Add ad to wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="adId",
     *         in="path",
     *         required=true,
     *         description="Ad ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Added to wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Already in wishlist"
     *     )
     * )
     */
    // POST /api/user/wishlist/{adId}
    public function addToWishlist($adId)
    {
        $user = auth()->user();
        $advert = Advert::find($adId);

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $existing = Wishlist::where('user_id', $user->user_id)
            ->where('advert_id', $adId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Already in wishlist'
            ], 409);
        }

        Wishlist::create([
            'user_id'   => $user->user_id,
            'advert_id' => $adId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist successfully'
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/user/wishlist/{adId}",
     *     summary="Remove ad from wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="adId",
     *         in="path",
     *         required=true,
     *         description="Ad ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Removed from wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not in wishlist"
     *     )
     * )
     */
    // DELETE /api/user/wishlist/{adId}
    public function removeFromWishlist($adId)
    {
        $user = auth()->user();

        $wishlistItem = Wishlist::where('user_id', $user->user_id)
            ->where('advert_id', $adId)
            ->first();

        if (!$wishlistItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item not in wishlist'
            ], 404);
        }

        $wishlistItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/wishlist/{adId}",
     *     summary="Toggle ad in wishlist (add/remove)",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="adId",
     *         in="path",
     *         required=true,
     *         description="Ad ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Added to wishlist"),
     *             @OA\Property(property="in_wishlist", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advert not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function toggleWishlist($adId)
    {
        $user = auth()->user();

        // Check if advert exists
        $advert = Advert::find($adId);
        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        // Check if already in wishlist
        $wishlist = Wishlist::where('user_id', $user->user_id)
            ->where('advert_id', $adId)
            ->first();

        if ($wishlist) {
            // Remove from wishlist
            $wishlist->delete();
            $message = 'Removed from wishlist';
            $inWishlist = false;
        } else {
            // Add to wishlist
            Wishlist::create([
                'user_id' => $user->user_id,
                'advert_id' => $adId
            ]);
            $message = 'Added to wishlist';
            $inWishlist = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'in_wishlist' => $inWishlist
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user/wishlist",
     *     summary="Get user's wishlist",
     *     tags={"Wishlist"},
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
     *         description="Items per page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist items",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/AdvertList")
     *         )
     *     )
     * )
     */
    public function wishlist(Request $request)
    {
        $user = auth()->user();

        $wishlist = Advert::with(['firstImage', 'car', 'phone'])
            ->join('wishlists', function ($join) use ($user) {
                $join->on('wishlists.advert_id', '=', 'adverts.id')
                     ->where('wishlists.user_id', $user->user_id);
            })
            ->orderBy('wishlists.created_at', 'desc')
            ->select('adverts.*')
            ->paginate($request->get('per_page', 20));

        $wishlist->getCollection()->transform(function ($ad) {
            return [
                'id'          => $ad->id,
                'ad_id'       => $ad->ad_id,
                'ad_title'    => $ad->ad_title,
                'description' => $ad->description,
                'price'       => $ad->price,
                'price_type'  => $ad->price_type,
                'state'       => $ad->state,
                'lga'         => $ad->lga,
                'condition'   => $ad->condition,
                'buy_direct'  => $ad->buy_direct,
                'sold'        => $ad->sold,
                'sold_date'   => $ad->sold_date,
                'featured'    => $ad->featured,
                'ad_status'   => $ad->ad_status,
                'category'    => $ad->category,
                'sub_category'=> $ad->sub_category,
                'created_at'  => $ad->created_at,
                'image_thumb' => $ad->firstImage
                    ? ($ad->firstImage->hasGeneratedConversion('thumbnail')
                        ? $ad->firstImage->getUrl('thumbnail')
                        : $ad->firstImage->getUrl())
                    : null,
                'image_large' => $ad->firstImage
                    ? ($ad->firstImage->hasGeneratedConversion('large')
                        ? $ad->firstImage->getUrl('large')
                        : $ad->firstImage->getUrl())
                    : null,
                'car'   => $ad->car,
                'phone' => $ad->phone,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $wishlist->items(),
            'pagination' => [
                'total'        => $wishlist->total(),
                'per_page'     => $wishlist->perPage(),
                'current_page' => $wishlist->currentPage(),
                'last_page'    => $wishlist->lastPage(),
                'has_more'     => $wishlist->hasMorePages(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/payments",
     *     summary="Get user's payments",
     *     tags={"Payments"},
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
     *         description="Items per page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment history",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PaymentList"),
     *             @OA\Property(property="completed_orders_count", type="integer", description="Count of orders with payment_status=paid, i.e. the buyer's order total")
     *         )
     *     )
     * )
     */
    public function payments(Request $request)
    {
        $user = auth()->user();

        $payments = Payment::with(['advert.firstImage', 'shipping'])
            ->where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        // Escrow status for each order (same source of truth as order details).
        $payments->getCollection()->transform(function (Payment $p) {
            $status = BuyDirectStatus::for($p, 'buyer');
            $p->setAttribute('status_badge', collect($status)->only(['stage', 'label', 'icon', 'color'])->all());
            $p->setAttribute('actions', $status['actions']);

            return $p;
        });

        $completedOrdersCount = Payment::where('user_id', $user->user_id)
            ->where('payment_status', 'paid')
            ->count();

        return response()->json([
            'success' => true,
            'data' => $payments,
            'completed_orders_count' => $completedOrdersCount
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/payments/{paymentId}/confirm-delivery",
     *     summary="Confirm delivery",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="paymentId",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery confirmed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function confirmDelivery($paymentId)
    {
        $payment = Payment::find($paymentId);

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        [$ok, $message] = BuyDirectEscrow::make()->confirmReceipt($payment, auth()->user()->user_id);

        return response()->json([
            'success' => $ok,
            'message' => $message,
            'data'    => $ok ? ['status' => BuyDirectStatus::for($payment, 'buyer'), 'escrow' => BuyDirectStatus::escrowFields($payment)] : null,
        ], $ok ? 200 : 422);
    }

    /**
     * POST /api/user/payments/{paymentId}/report-problem
     * Buyer reports a problem; holds the payment until an admin resolves it.
     */
    public function reportProblem($paymentId, Request $request)
    {
        $validator = Validator::make($request->all(), ['reason' => 'required|string|min:10|max:2000']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $payment = Payment::find($paymentId);
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        [$ok, $message] = BuyDirectEscrow::make()->openDispute($payment, auth()->user()->user_id, $request->input('reason'));

        return response()->json(['success' => $ok, 'message' => $message], $ok ? 200 : 422);
    }
    /**
     * @OA\Post(
     *     path="/api/user/payments/{paymentId}/cancel-order",
     *     summary="Cancel order (buyer)",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="paymentId",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order canceled",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Order can no longer be canceled"
     *     )
     * )
     */
    public function cancelOrder($paymentId)
    {
        $payment = Payment::find($paymentId);

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        [$ok, $message] = BuyDirectEscrow::make()->cancel($payment, 'buyer', 'Canceled by the buyer before shipping.', auth()->user()->user_id);

        return response()->json(['success' => $ok, 'message' => $message], $ok ? 200 : 422);
    }
    /**
     * @OA\Patch(
     *     path="/api/user/payments/{paymentId}/shipping-status",
     *     summary="Update shipping status",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="paymentId",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"seller_status"},
     *             @OA\Property(property="seller_status", type="string", enum={"shipped","canceled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Shipping status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function updateShippingStatus($paymentId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seller_status' => 'required|in:shipped,canceled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::find($paymentId);
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $sellerId = auth()->user()->user_id;
        $escrow = BuyDirectEscrow::make();
        [$ok, $message] = $request->seller_status === 'shipped'
            ? $escrow->markShipped($payment, $sellerId)
            : $escrow->cancel($payment, 'seller', 'Canceled by the seller before shipping.', $sellerId);

        return response()->json([
            'success' => $ok,
            'message' => $message,
            'data'    => $ok ? ['status' => BuyDirectStatus::for($payment, 'seller'), 'escrow' => BuyDirectStatus::escrowFields($payment)] : null,
        ], $ok ? 200 : 422);
    }
    /**
     * GET /api/user/payments/{paymentId}/details
     * Buyer-facing order details (equivalent to web /user/order-details/{id}).
     * Returns full order info: product, shipping company, delivery location, order summary.
     */
    public function orderDetails($paymentId)
    {
        $user = auth()->user();

        $payment = Payment::with(['advert.media', 'stateLocation'])
            ->where('id', $paymentId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        $advert = $payment->advert;
        $status = BuyDirectStatus::for($payment, 'buyer');

        return response()->json([
            'success' => true,
            'data'    => [
                'order' => [
                    'id'                 => $payment->id,
                    'order_code'         => $payment->order_code,
                    'reference'          => $payment->payment_reference,
                    'amount_paid'        => $payment->amount_paid,
                    'payment_status'     => $payment->payment_status,
                    'seller_status'      => $payment->seller_status ?? 'pending',
                    'buyer_status'       => $payment->buyer_status ?? 'pending',
                    'seller_status_date' => $payment->seller_status_date,
                    'created_at'         => $payment->created_at,
                ],
                // Computed display fields — render these directly, don't
                // re-implement the escrow state machine client-side. See
                // App\Support\BuyDirectStatus (shared with the web order page).
                'status_badge'  => collect($status)->only(['stage', 'label', 'icon', 'color', 'class'])->all(),
                'status_banner' => $status['banner'],
                'actions'       => $status['actions'],  // can_confirm_receipt, can_cancel, can_report_problem
                'escrow'        => BuyDirectStatus::escrowFields($payment),
                'delivery'      => BuyDirectStatus::deliveryFields($payment),
                'product' => [
                    'name'          => $advert->ad_title ?? null,
                    'price'         => $advert->price ?? null,
                    'state'         => $advert->state ?? null,
                    'thumbnail_url' => $advert && $advert->hasMedia('images')
                                        ? $advert->getFirstMediaUrl('images', 'thumbnail')
                                        : null,
                    'ad_url'        => $advert
                                        ? url(($advert->state_slug ?? '') . '/' . ($advert->title_slug ?? '') . '/' . $advert->ad_id)
                                        : null,
                ],
            ]
        ]);
    }
    /**
     * GET /api/user/adverts/{advertId}/shipping-details
     * Returns all seller-facing shipping details for a sold ad (equivalent to web /user/ad-shipping/{id}).
     */
    public function adShippingDetails($advertId)
    {
        $user = auth()->user();

        $payment = Payment::with(['advert.media', 'stateLocation'])
            ->where('advert_id', $advertId)
            ->where('payment_status', 'paid')
            ->latest()
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'No order found for this ad.'
            ], 404);
        }

        // Only the ad owner can view the order
        if (!$payment->advert || $payment->advert->user_id !== $user->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorised.'
            ], 403);
        }

        $advert = $payment->advert;
        $status = BuyDirectStatus::for($payment, 'seller');

        return response()->json([
            'success' => true,
            'data'    => [
                'payment_id'    => $payment->id,
                'order_code'    => $payment->order_code,
                'advert_id'     => $advert->id,
                'ad_id'         => $advert->ad_id,
                'buyer_status'  => $payment->buyer_status ?? 'pending',
                'seller_status' => $payment->seller_status ?? 'pending',
                'status_badge'  => collect($status)->only(['stage', 'label', 'icon', 'color', 'class'])->all(),
                'status_banner' => $status['banner'],
                // can_mark_shipped → PATCH shipping-status {seller_status: shipped}
                // can_cancel       → PATCH shipping-status {seller_status: canceled}
                'actions'       => $status['actions'],
                'escrow'        => BuyDirectStatus::escrowFields($payment),
                'payout_amount' => (float) ($payment->payout_amount ?? $payment->amount),
                'has_bank_details' => !empty($user->account_number) && !empty($user->bank_code),
                'buyer'         => BuyDirectStatus::deliveryFields($payment),
                'product' => [
                    'name'          => $advert->ad_title,
                    'thumbnail_url' => $advert->getFirstMediaUrl('images', 'thumbnail'),
                    'ad_url'        => url(($advert->state_slug ?? '') . '/' . ($advert->title_slug ?? '') . '/' . $advert->ad_id),
                ],
            ]
        ]);
    }
    /**
     * @OA\Patch(
     *     path="/api/user/ads/{adId}/mark-sold",
     *     summary="Mark ad as sold",
     *     tags={"Ads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="adId",
     *         in="path",
     *         required=true,
     *         description="Ad ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ad marked as sold",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ad not found"
     *     )
     * )
     */
    public function markAsSold($adId)
    {
        $user = auth()->user();
        $advert = Advert::where('id', $adId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $advert->update([
            'sold' => "Yes",
            'sold_date' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Advert marked as sold'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/feedbacks",
     *     summary="Get user's feedbacks",
     *     tags={"Feedbacks"},
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
     *         description="Items per page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Feedbacks received",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Feedback"))
     *         )
     *     )
     * )
     */
    public function feedbacks(Request $request)
    {
        $user = auth()->user();

        $feedbacks = Feedback::with('user')
            ->where('seller_id', $user->user_id)
            ->orderBy('created_at', 'asc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $feedbacks
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/feedbacks/{sellerId}",
     *     summary="Submit feedback for seller",
     *     tags={"Feedbacks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sellerId",
     *         in="path",
     *         required=true,
     *         description="Seller ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating", "satisfaction", "reliable", "friendly", "message"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="satisfaction", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="reliable", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="friendly", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="message", type="string", maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Feedback submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function submitFeedback($sellerId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5',
            'satisfaction' => 'required|integer|between:1,5',
            'reliable' => 'required|integer|between:1,5',
            'friendly' => 'required|integer|between:1,5',
            'message' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        Feedback::updateOrCreate(
            [
                'user_id' => $user->user_id,
                'seller_id' => $sellerId
            ],
            array_merge($request->all(), [
                'user_id' => $user->user_id,
                'seller_id' => $sellerId
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/following/check/{userId}",
     *     summary="Check if following user",
     *     tags={"Following"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID to check",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Following status",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="isFollowing", type="boolean")
     *         )
     *     )
     * )
     */
    public function checkFollowing($userId)
    {
        $user = auth()->user();

        $isFollowing = Followers::where([
            'user_id' => $user->user_id,
            'follow' => $userId
        ])->exists();

        return response()->json([
            'success' => true,
            'isFollowing' => $isFollowing
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/following/toggle",
     *     summary="Toggle follow status",
     *     tags={"Following"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"followee_id"},
     *             @OA\Property(property="followee_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Follow status toggled",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="isFollowing", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    // PUT /api/user/notifications/read-all
    public function markAllNotificationsAsRead()
    {
        $user = auth()->user();

        $user->notifications_seen_at = now();
        $user->save();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    // PUT /api/user/notifications/{id}/read
    public function markNotificationAsRead($id)
    {
        $user = auth()->user();

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    // DELETE /api/user/delete-notification/{id}
    public function deleteNotification($id)
    {
        $user = auth()->user();

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    public function toggleFollow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'followee_id' => 'required|exists:users,user_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $followeeId = $request->followee_id;

        $follow = Followers::where([
            'user_id' => $user->user_id,
            'follow' => $followeeId
        ])->first();

        if ($follow) {
            $follow->delete();
            return response()->json([
                'success' => true,
                'isFollowing' => false,
                'message' => 'Unfollowed successfully'
            ]);
        } else {
            Followers::create([
                'user_id' => $user->user_id,
                'follow' => $followeeId
            ]);
            return response()->json([
                'success' => true,
                'isFollowing' => true,
                'message' => 'Followed successfully'
            ]);
        }
    }

    // POST /api/user/payments/{paymentId}/resume
    // Resume an abandoned pending payment — returns a fresh Flutterwave authorization_url
    public function resumePayment($paymentId)
    {
        $user = auth()->user();

        $payment = Payment::with('advert')
            ->where('id', $paymentId)
            ->where('user_id', $user->user_id)
            ->where('payment_status', 'pending')
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Pending payment not found.'
            ], 404);
        }

        if ($payment->advert && $payment->advert->sold === 'Yes') {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, this item has been sold to another buyer.'
            ], 409);
        }

        $txRef = 'MKT-' . strtoupper(Str::random(20));

        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/payments', [
                'tx_ref'        => $txRef,
                'amount'        => round($payment->amount_paid),
                'currency'      => 'ETB',
                'redirect_url'  => config('app.url') . '/api/payments/callback',
                'customer'      => [
                    'email' => $user->email,
                    'name'  => $user->name,
                ],
                'meta'          => [
                    'advert_id'    => $payment->advert_id,
                    'user_id'      => $user->user_id,
                    'payment_type' => 'buy_direct',
                ],
                'customizations' => [
                    'title' => config('global.site_name'),
                ],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) !== 'success') {
            return response()->json([
                'success' => false,
                'message' => 'Could not initialize payment. Please try again.'
            ], 502);
        }

        $payment->update(['payment_reference' => $txRef]);

        return response()->json([
            'success'           => true,
            'authorization_url' => $data['data']['link'],
            'reference'         => $txRef,
            'amount'            => $payment->amount_paid,
            'payment_id'        => $payment->id,
        ]);
    }

    // DELETE /api/user/following/remove/{userId}
    // Remove a specific person from your own followers list
    public function removeFollower($userId)
    {
        $user = auth()->user();

        $deleted = Followers::where('user_id', $userId)
            ->where('follow', $user->user_id)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Follower removed'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Follower not found'
        ], 404);
    }
}
