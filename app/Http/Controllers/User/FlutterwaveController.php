<?php

namespace App\Http\Controllers\User;
use App\Services\BuyDirectCheckout;
use App\Services\BuyDirectEscrow;
use App\Services\BuyDirectSettings;
use App\Services\FlutterwaveGateway;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Payment;
use App\Models\User;
use App\Models\Advert;
use App\Models\AdvertBoost;
use Carbon\Carbon;
use Mail;
use App\Mail\BuyDirectMail;
use App\Mail\SellerMail;
use App\Models\State;
use App\Models\GigLogistic;
use App\Models\Shipping;
use App\Models\Notification;
use App\Jobs\SendAdSoldPushNotification;
use App\Traits\HasUserSession;
use Illuminate\Support\Facades\Log;
use App\Rules\EthiopianPhoneNumber;

class FlutterwaveController extends Controller
{
    use HasUserSession;

    private function generateTxRef(): string
    {
        return 'MKT-' . strtoupper(Str::random(20));
    }

    public function initialize(Request $request)
    {
        $checkout = session('buy_direct_checkout');
        if (!$checkout) {
            return redirect('/')->with('error', 'Your checkout session has expired. Please start again.');
        }

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $advert = Advert::find($checkout['advert_id']);

        if ($error = BuyDirectCheckout::eligibilityError($advert, $user)) {
            return redirect()->back()->with('error', $error);
        }

        // Always charge the server-side price, never anything posted by the form.
        $checkout = array_merge($checkout, BuyDirectSettings::quote((float) $advert->price));

        // Backfill the profile phone from the checkout form if it's missing,
        // so buyers/sellers can always be reached (see admin contact details).
        if (empty($user->phone) && !empty($checkout['phone'])) {
            $checkoutPhone = trim($checkout['phone']);
            $rule = new EthiopianPhoneNumber();

            if ($rule->passes('phone', $checkoutPhone) && !User::where('phone', $checkoutPhone)->where('user_id', '!=', $user_id)->exists()) {
                $user->update(['phone' => $checkoutPhone]);
            }
        }

        $txRef = $this->generateTxRef();

        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/payments', [
                'tx_ref' => $txRef,
                'amount' => round($checkout['grand_total']),
                'currency' => 'ETB',
                'redirect_url' => route('flutterwave.callback'),
                'customer' => [
                    'email' => $user->email,
                    'name' => $user->name,
                    'phonenumber' => $checkout['phone'],
                ],
                'meta' => [
                    'advert_id' => $advert->id,
                    'user_id' => $user_id,
                    'payment_type' => 'buy_direct',
                ],
                'customizations' => [
                    'title' => config('global.site_name'),
                ],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {
            BuyDirectCheckout::upsertPendingOrder($checkout, $user_id, $txRef, 'web');
            $request->session()->forget('buy_direct_checkout');

            return redirect($data['data']['link']);
        }

        return back()->with('error', 'Payment initialization failed.');
    }


    public function callback(Request $request)
    {
        $reference = $request->tx_ref;

        $booking = $reference ? Payment::where('payment_reference', $reference)->first() : null;

        if (!$booking) {
            return redirect()->route('payment.failed')->with('error', 'Booking not found.');
        }

        // Verify with Flutterwave — never trust the redirect query string.
        $verified = app(FlutterwaveGateway::class)->verifyByReference($reference);

        if ($verified['ok'] && $verified['status'] === 'successful') {
            $escrow = BuyDirectEscrow::make();
            $escrow->markPaid($booking, $verified['data']);
            $booking->refresh();

            if ($booking->payment_status !== 'paid') {
                return redirect()->route('payment.failed')->with('error', 'We could not confirm the full payment amount. Our team has been notified.');
            }

            return redirect()->route('buy.direct.success')->with([
                'ad_title'   => $booking->advert->ad_title ?? '',
                'order_code' => $booking->order_code,
                'order_id'   => $booking->id,
                'ship_by'    => optional($booking->ship_by)->format('d M Y'),
                'amount'     => number_format($booking->amount_paid),
            ]);
        }

        return redirect()->route('payment.failed')->with('error', 'Payment not successful.');
    }

    public function failed()
    {
        $title = "Payment Failed  | " . config('global.site_name');
        return view('public.payment-failed', compact('title'));
    }

    public function success()
    {
        $title = "Payment Successful  | " . config('global.site_name');
        return view('public.payment-success', compact('title'));
    }

    public function buyDirectSuccess()
    {
        $title = "Order Confirmed  | " . config('global.site_name');
        return view('public.buy-direct-success', compact('title'));
    }

    public function resumePayment(Request $request, $paymentId)
    {
        $userId = $request->session()->get('user_id');

        $payment = Payment::with('advert')
            ->where('id', $paymentId)
            ->where('user_id', $userId)
            ->where('payment_status', 'pending')
            ->first();

        if (!$payment) {
            return redirect()->route('user.payments')->with('error', 'Payment not found.');
        }

        if ($payment->advert && $payment->advert->sold === 'Yes') {
            return redirect()->route('user.payments')->with('error', 'Sorry, this item has been sold to another buyer.');
        }

        $user = User::where('user_id', $userId)->first();
        $txRef = $this->generateTxRef();

        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/payments', [
                'tx_ref' => $txRef,
                'amount' => round($payment->amount_paid),
                'currency' => 'ETB',
                'redirect_url' => route('flutterwave.callback'),
                'customer' => [
                    'email' => $user->email,
                    'name' => $user->name,
                ],
                'meta' => [
                    'advert_id'    => $payment->advert_id,
                    'user_id'      => $userId,
                    'payment_type' => 'buy_direct',
                ],
                'customizations' => [
                    'title' => config('global.site_name'),
                ],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {
            $payment->update(['payment_reference' => $txRef]);
            return redirect($data['data']['link']);
        }

        return redirect()->route('user.payments')->with('error', 'Could not resume payment. Please try again.');
    }


    public function initialize_boost(Request $request)
    {
        $advertId = $request->advert_id;
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $email = $user->email;
        $amount = (int) str_replace(',', '', $request->amount);
        if (!AdvertBoost::claimInitSlot($user_id, $advertId)) {
            return back()->with('error', 'Your boost payment is already being set up. Please wait a few seconds and try again.');
        }

        $txRef = $this->generateTxRef();

        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/payments', [
                'tx_ref' => $txRef,
                'amount' => $amount,
                'currency' => 'ETB',
                'redirect_url' => route('boost.callback'),
                'customer' => [
                    'email' => $email,
                    'name' => $user->name,
                ],
                'meta' => [
                    'advert_id'    => $advertId,
                    'user_id'      => $user_id,
                    'payment_type' => 'boost',
                ],
                'customizations' => [
                    'title' => config('global.site_name'),
                ],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {

            $post = AdvertBoost::create([
                'advert_id'         => $advertId,
                'user_id'           => $user_id,
                'payment_reference' => $txRef,
                'amount'            => $amount,
                'boost_type'        => $request->input('boost_name'),
                'duration'          => $request->input('duration'),
                'boost_status'      => 'pending',
                'payment_status'    => 'pending',
            ]);

            return redirect($data['data']['link']);
        }

        return back()->with('error', 'Payment initialization failed.');
    }

    public function callback_boost(Request $request)
    {
        $reference = $request->tx_ref;

        $boost = AdvertBoost::where('payment_reference', $reference)->first();

        if (!$boost) {
            Log::warning('Boost callback: reference not found', ['ref' => $reference]);
            return redirect()->route('payment.failed')->with('error', 'Advert to boost not found.');
        }

        // Idempotency: webhook may have already activated this boost
        if ($boost->payment_status === 'paid' && $boost->boost_status === 'active') {
            return redirect()->route('payment.success');
        }

        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->get(config('services.flutterwave.paymentUrl') . "/transactions/verify_by_reference", [
                'tx_ref' => $reference,
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success' && ($data['data']['status'] ?? '') === 'successful') {
            $transactionId = $data['data']['id'];

            DB::transaction(function () use ($boost, $transactionId) {
                $boost->update([
                    'payment_status' => 'paid',
                    'boost_status'   => 'active',
                    'trans_id'       => $transactionId,
                    'start_date'     => Carbon::now(),
                ]);

                DB::table('adverts')
                    ->where('id', $boost->advert_id)
                    ->update(['featured' => 'Yes']);
            });

            Log::info('Boost callback: activated', [
                'boost_id'  => $boost->id,
                'advert_id' => $boost->advert_id,
                'trans_id'  => $transactionId,
            ]);

            return redirect()->route('payment.success');
        }

        Log::warning('Boost callback: verification failed', ['ref' => $reference]);
        return redirect()->route('payment.failed')->with('error', 'Payment was not successful.');
    }

    public function initialize_post_boost(Request $request)
    {
        //dd($request);
        $advertId = $request->advert_id;
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $email = $user->email;
        if (!AdvertBoost::claimInitSlot($user_id, $advertId)) {
            return back()->with('error', 'Your boost payment is already being set up. Please wait a few seconds and try again.');
        }

        $promotion = $request->session()->get('promotion');
        $duration = $request->input('duration');
        $txRef = $this->generateTxRef();
        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/payments', [
                'tx_ref' => $txRef,
                'amount' => round($request->amount),
                'currency' => 'ETB',
                'redirect_url' => route('boost.callback'),
                'customer' => [
                    'email' => $email,
                    'name' => $user->name,
                ],
                'meta' => [
                    'advert_id'    => $advertId,
                    'user_id'      => $user_id,
                    'payment_type' => 'boost',
                ],
                'customizations' => [
                    'title' => config('global.site_name'),
                ],
            ]);

        $data = $response->json();

        if($promotion == 'top'){
            $duration = 14;
        } elseif($promotion == 'gallery'){
            $duration = 14;
        } else {
            $duration = 7;
        }

        if (($data['status'] ?? null) === 'success') {

            $post = AdvertBoost::create([
                'advert_id'=> $advertId,
                'user_id'=> $user_id,
                'payment_reference'=> $txRef,
                'amount'=> $request->input('amount'),
                'boost_type'=> $promotion,
                'duration'=> $duration,
                'boost_status'=> "pending",
                'payment_status'=> "pending",
            ]);

            return redirect($data['data']['link']);
        }

        return back()->with('error', 'Payment initialization failed.');
    }


    public function retry_boost_payment(Request $request)
{
    $boost_id = $request->boost_id;
    $user_id = $request->session()->get('user_id');

    // Find the boost record
    $boost = AdvertBoost::where('id', $boost_id)
        ->where('user_id', $user_id) // Ensure user owns this boost
        ->first();

    if (!$boost) {
        return back()->with('error', 'Boost record not found.');
    }

    // Check if payment is already completed
    if ($boost->payment_status === 'paid') {
        return back()->with('error', 'This boost has already been paid for.');
    }

    // Get user email
    $user = User::where('user_id', $user_id)->first();
    $email = $user->email;
    $txRef = $this->generateTxRef();

    // Initialize payment with a NEW reference
    $response = Http::withToken(config('services.flutterwave.secretKey'))
        ->post(config('services.flutterwave.paymentUrl') . '/payments', [
            'tx_ref' => $txRef,
            'amount' => $boost->amount,
            'currency' => 'ETB',
            'redirect_url' => route('boost.callback'),
            'customer' => [
                'email' => $email,
                'name' => $user->name,
            ],
            'meta' => [
                'advert_id' => $boost->advert_id,
                'user_id' => $user_id,
                'boost_id' => $boost_id, // Add this to link back to the boost record
            ],
            'customizations' => [
                'title' => config('global.site_name'),
            ],
        ]);

    $data = $response->json();

    if (($data['status'] ?? null) === 'success') {
        // Update the boost record with the new reference
        $boost->update([
            'payment_reference' => $txRef,
        ]);

        return redirect($data['data']['link']);
    }

    return back()->with('error', 'Payment initialization failed. Please try again.');
}


}
