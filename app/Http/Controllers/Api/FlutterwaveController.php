<?php

namespace App\Http\Controllers\Api;

use App\Services\BuyDirectCheckout;
use App\Services\BuyDirectEscrow;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\State;
use App\Models\GigLogistic;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\BuyDirectMail;
use App\Mail\SellerMail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * @group Payments
 *
 * APIs for payment processing, Flutterwave integration, and boost purchases
 */
class FlutterwaveController extends Controller
{
    private function generateTxRef(): string
    {
        return 'MKT-' . strtoupper(Str::random(20));
    }

    /**
     * @OA\Post(
     *     path="/api/payments/initialize",
     *     summary="Initialize payment for buy directly",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"advert_id","first_name","last_name","phone","state","lga","address"},
     *             @OA\Property(property="advert_id", type="integer"),
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="phone", type="string", example="0912345678"),
     *             @OA\Property(property="state", type="integer", description="Region id"),
     *             @OA\Property(property="lga", type="string", description="Zone / city name within the region"),
     *             @OA\Property(property="address", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment initialized successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="authorization_url", type="string"),
     *             @OA\Property(property="reference", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function initializePayment(Request $request)
    {
        // Accept the flat checkout object returned by /api/shipping/calculate,
        // or the same keys nested under shipping_data (older app builds).
        $input = $request->has('shipping_data') && is_array($request->shipping_data)
            ? array_merge($request->shipping_data, ['advert_id' => $request->input('advert_id', data_get($request->shipping_data, 'ad.id'))])
            : $request->all();

        $user = auth()->user();
        $advert = Advert::find($input['advert_id'] ?? null);

        if ($error = BuyDirectCheckout::eligibilityError($advert, $user)) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        $validator = Validator::make($input, BuyDirectCheckout::rules(), BuyDirectCheckout::messages());
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $valid = $validator->validated();
        if ($lgaError = BuyDirectCheckout::lgaError((int) $valid['state'], $valid['lga'])) {
            return response()->json(['success' => false, 'errors' => ['lga' => [$lgaError]]], 422);
        }

        // Amounts are always computed server-side; anything the client sent is ignored.
        $checkout = BuyDirectCheckout::build($advert, $valid);
        $txRef = $this->generateTxRef();

        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/payments', [
                'tx_ref' => $txRef,
                'amount' => round($checkout['grand_total']),
                'currency' => 'ETB',
                'redirect_url' => config('app.url') . '/api/payments/callback',
                'customer' => [
                    'email' => $user->email,
                    'name' => $user->name,
                    'phonenumber' => $checkout['phone'],
                ],
                'meta' => [
                    'advert_id' => $advert->id,
                    'user_id' => $user->user_id,
                    'payment_type' => 'buy_direct'
                ],
                'customizations' => [
                    'title' => config('global.site_name'),
                ],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {
            $payment = BuyDirectCheckout::upsertPendingOrder($checkout, $user->user_id, $txRef, 'api');

            return response()->json([
                'success' => true,
                'authorization_url' => $data['data']['link'],
                'reference' => $txRef,
                'payment_id' => $payment->id,
                'order_code' => $payment->order_code,
                'amount' => $checkout['grand_total'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment initialization failed'
        ], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/callback",
     *     summary="Handle payment callback from Flutterwave",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reference"},
     *             @OA\Property(property="reference", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Payment failed"
     *     )
     * )
     */
    public function handleCallback(Request $request)
    {
        $reference = $request->input('reference') ?? $request->input('tx_ref');

        $validator = Validator::make(['reference' => $reference], [
            'reference' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->get(config('services.flutterwave.paymentUrl') . "/transactions/verify_by_reference", [
                'tx_ref' => $reference,
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success' && ($data['data']['status'] ?? '') === 'successful') {
            $transactionId = $data['data']['id'];
            $amount = $data['data']['amount'];
            $metadata = $data['data']['meta'] ?? [];
            $paymentType = $metadata['payment_type'] ?? 'buy_direct';

            if ($paymentType === 'buy_direct') {
                return $this->handleBuyDirectPayment($reference, $transactionId, $amount, $metadata, $data['data']);
            } elseif ($paymentType === 'boost') {
                return $this->handleBoostPayment($reference, $transactionId, $amount, $metadata);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unknown payment type'
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment verification failed'
        ], 400);
    }

    private function handleBuyDirectPayment($reference, $transactionId, $amount, $metadata, array $tx = [])
    {
        $payment = Payment::where('payment_reference', $reference)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found'
            ], 404);
        }

        BuyDirectEscrow::make()->markPaid($payment, $tx ?: ['id' => $transactionId, 'amount' => $amount, 'currency' => 'ETB']);
        $payment->refresh();

        if ($payment->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'We could not confirm the full payment amount. Our team has been notified.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment successful',
            'payment_id' => $payment->id,
            'order_code' => $payment->order_code,
            'ship_by' => optional($payment->ship_by)->toIso8601String(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/initialize-boost",
     *     summary="Initialize payment for advert boost",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"advert_id", "amount", "boost_type", "duration"},
     *             @OA\Property(property="advert_id", type="integer"),
     *             @OA\Property(property="amount", type="number"),
     *             @OA\Property(property="boost_type", type="string"),
     *             @OA\Property(property="duration", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Boost payment initialized successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="authorization_url", type="string"),
     *             @OA\Property(property="reference", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function initializeBoost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'advert_id' => 'required|integer|exists:adverts,id',
            'amount' => 'required|numeric|min:0',
            'boost_type' => 'required|string',
            'duration' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $amount = (int) str_replace(',', '', $request->amount);

        if (!AdvertBoost::claimInitSlot($user->user_id, $request->advert_id)) {
            return response()->json([
                'success' => false,
                'message' => 'A boost payment for this advert is already being set up. Please wait a few seconds and try again.'
            ], 429);
        }

        $txRef = $this->generateTxRef();

        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/payments', [
                'tx_ref' => $txRef,
                'amount' => $amount,
                'currency' => 'ETB',
                'redirect_url' => config('app.url') . '/api/payments/callback',
                'customer' => [
                    'email' => $user->email,
                    'name' => $user->name,
                ],
                'meta' => [
                    'advert_id' => $request->advert_id,
                    'user_id' => $user->user_id,
                    'payment_type' => 'boost',
                    'boost_type' => $request->boost_type,
                    'duration' => $request->duration
                ],
                'customizations' => [
                    'title' => config('global.site_name'),
                ],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {
            $boost = AdvertBoost::create([
                'advert_id' => $request->advert_id,
                'user_id' => $user->user_id,
                'payment_reference' => $txRef,
                'amount' => $amount,
                'boost_type' => $request->boost_type,
                'duration' => $request->duration,
                'boost_status' => "pending",
                'payment_status' => "pending",
            ]);

            return response()->json([
                'success' => true,
                'authorization_url' => $data['data']['link'],
                'reference' => $txRef,
                'boost_id' => $boost->id
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Boost payment initialization failed'
        ], 400);
    }

    private function handleBoostPayment($reference, $transactionId, $amount, $metadata)
    {
        $boost = AdvertBoost::where('payment_reference', $reference)->first();

        if (!$boost) {
            return response()->json([
                'success' => false,
                'message' => 'Boost record not found'
            ], 404);
        }

        DB::transaction(function () use ($boost, $transactionId, $amount) {
            $boost->update([
                'payment_status' => 'paid',
                'boost_status' => 'active',
                'trans_id' => $transactionId,
                'start_date' => Carbon::now(),
            ]);

            Advert::where('id', $boost->advert_id)->update([
                'featured' => "Yes",
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Boost payment successful'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/payments/{paymentId}",
     *     summary="Get payment details",
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
     *         description="Payment details",
     *         @OA\JsonContent(ref="#/components/schemas/Payment")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function getPayment($paymentId)
    {
        $payment = Payment::with(['advert', 'user'])->find($paymentId);
        $me = auth()->user()->user_id;

        // Only the buyer or the seller of this order may view it.
        if ($payment && $payment->user_id !== $me && optional($payment->advert)->user_id !== $me) {
            $payment = null;
        }

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/payments/user/{userId}",
     *     summary="Get user's payment history",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User payment history",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment"))
     *         )
     *     )
     * )
     */
    public function getUserPayments($userId, Request $request)
    {
        // Orders carry buyer contact and delivery details — only the owner may list them.
        if ((string) $userId !== (string) auth()->user()->user_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorised.'], 403);
        }

        $payments = Payment::with(['advert'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/boosts/user/{userId}",
     *     summary="Get user's boost history",
     *     tags={"Boosts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User boost history",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AdvertBoost"))
     *         )
     *     )
     * )
     */
    public function getUserBoosts($userId, Request $request)
    {
        $boosts = AdvertBoost::with(['advert'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $boosts
        ]);
    }
}
