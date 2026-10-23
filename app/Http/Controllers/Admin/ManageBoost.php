<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminHelper;
use App\Http\Controllers\Controller;
use App\Models\Advert;
use App\Models\BoostDuration;
use App\Models\BoostType;
use App\Models\UnmatchedPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\AdvertBoost;
use Carbon\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ManageBoost extends Controller
{
    public function active(Request $request)
    {
        $title = "Active Boost Adverts | " . config('global.site_name');
        $page_title = "Active Boost Adverts";

        $adverts = AdvertBoost::with(['user', 'advert.firstImage'])
            ->whereHas('advert', function ($query) {
                $query->where('ad_status', 1)->where('sold', 'No');
            })
            ->whereNotNull('start_date')
            ->where('payment_status','paid')
            ->where('boost_status','active')
            ->whereRaw("DATE_ADD(start_date, INTERVAL CAST(duration AS UNSIGNED) DAY) > ?", [now()])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            //dd($adverts);
            return view('admin.adboost.index', compact('title', 'page_title', 'adverts'));
    }

    public function status($id, $status)
    {
        $advert = AdvertBoost::where('id',$id)->first();
        $advert_id = $advert->advert_id;
        if($status=='pending'){
            $boost_status = "pending";
            $featured = "No";
        }else{
            $boost_status = "active";
            $featured = "Yes";
        }
        //dd($featured);
        DB::table('advert_boosts')
                ->where('id', $id)
                ->update([
                    'boost_status'=> $status,
                    'updated_at' => Carbon::now(),
                ]);

        DB::table('adverts')
                ->where('id', $advert_id)
                ->update([
                    'featured'=> $featured,
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('status', ['text'=>'Advert Boost Updated','type'=>'success']);
    }

    public function completed(Request $request)
    {
        $title = "Completed Boost Adverts | " . config('global.site_name');
        $page_title = "Completed Boost Adverts";

        $adverts = AdvertBoost::with(['user', 'advert.firstImage'])
            ->whereHas('advert', function ($query) {
                $query->where('ad_status', 1)->where('sold', 'No');
            })
            ->whereNotNull('start_date')
            ->where('payment_status','paid')
            ->where('boost_status','completed')
            ->whereRaw("DATE_ADD(start_date, INTERVAL CAST(duration AS UNSIGNED) DAY) < ?", [now()])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.adboost.completed', compact('title', 'page_title', 'adverts'));
    }

    public function unpaid(Request $request)
    {
        $title = "Unpaid Boost Adverts | " . config('global.site_name');
        $page_title = "Unpaid Boost Adverts";

        // No proof uploaded yet — nothing for an admin to confirm here, see confirmPayment().
        $adverts = AdvertBoost::with(['user', 'advert.firstImage'])
            ->whereHas('advert', function ($query) {
                $query->where('ad_status', 1)->where('sold', 'No');
            })
            ->where('payment_status','pending')
            ->where('boost_status','pending')
            ->where('upload_proof', 'no')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.adboost.unpaid', compact('title', 'page_title', 'adverts'));
    }

    public function confirmPayment(Request $request)
    {
        $title = "Confirm Payment | " . config('global.site_name');
        $page_title = "Confirm Payment";

        // Manual bank-transfer proof was uploaded and is still awaiting admin review.
        $adverts = AdvertBoost::with(['user', 'advert.firstImage'])
            ->whereHas('advert', function ($query) {
                $query->where('ad_status', 1)->where('sold', 'No');
            })
            ->where('payment_status', 'pending')
            ->where('boost_status', 'pending')
            ->where('upload_proof', 'yes')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.adboost.confirm-payment', compact('title', 'page_title', 'adverts'));
    }

    public function paid(Request $request)
    {
        $title = "Paid Boost Adverts | " . config('global.site_name');
        $page_title = "Paid Boost Adverts";

        $adverts = AdvertBoost::with(['user', 'advert.firstImage'])
            ->whereHas('advert', function ($query) {
                $query->where('ad_status', 1)
                      ->where('sold', 'No');
            })
            ->where('payment_status', 'paid')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.adboost.paid', compact('title', 'page_title', 'adverts'));
    }


    public function payment($id, $status)
    {
        $boost = AdvertBoost::where('id', $id)->first();
        $advert_id = $boost->advert_id;

        // Always set start_date = now so the user gets their full boost duration from today
        $startDate  = Carbon::now();
        $expiresAt  = $startDate->copy()->addDays((int) $boost->duration);
        $alreadyExpired = $expiresAt->isPast();

        DB::table('advert_boosts')
            ->where('id', $id)
            ->update([
                'payment_status' => 'paid',
                'boost_status'   => $alreadyExpired ? 'completed' : 'active',
                'trans_id'       => 'manual-' . $id,
                'start_date'     => $startDate,
                'updated_at'     => Carbon::now(),
            ]);

        if (!$alreadyExpired) {
            DB::table('adverts')
                ->where('id', $advert_id)
                ->update(['featured' => 'Yes', 'updated_at' => Carbon::now()]);
        }

        return redirect()->back()->with('status', ['text' => 'Advert Boost Updated', 'type' => 'success']);
    }

    public function verifyAndActivate($id)
    {
        $boost = AdvertBoost::findOrFail($id);

        if ($boost->payment_status === 'paid') {
            return redirect()->back()->with('status', ['text' => 'This boost is already active.', 'type' => 'info']);
        }

        if (!$boost->payment_reference) {
            return redirect()->back()->with('status', ['text' => 'No payment reference on record — cannot verify with Flutterwave.', 'type' => 'error']);
        }

        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->get(config('services.flutterwave.paymentUrl') . "/transactions/verify_by_reference", [
                'tx_ref' => $boost->payment_reference,
            ])
            ->json();

        if (($response['status'] ?? null) !== 'success' || ($response['data']['status'] ?? '') !== 'successful') {
            $flutterwaveMessage = $response['data']['processor_response'] ?? ($response['message'] ?? 'Payment not confirmed');
            return redirect()->back()->with('status', [
                'text' => "Flutterwave says: {$flutterwaveMessage}. Boost not activated.",
                'type' => 'error',
            ]);
        }

        $transactionId = $response['data']['id'];

        DB::transaction(function () use ($boost, $transactionId) {
            $boost->update([
                'payment_status' => 'paid',
                'boost_status'   => 'active',
                'trans_id'       => $transactionId,
                'start_date'     => Carbon::now(),
            ]);

            Advert::where('id', $boost->advert_id)->update(['featured' => 'Yes']);
        });

        Log::info('Admin verify-and-activate: boost activated', [
            'boost_id'  => $boost->id,
            'advert_id' => $boost->advert_id,
            'trans_id'  => $transactionId,
        ]);

        return redirect()->back()->with('status', ['text' => 'Payment verified with Flutterwave — boost is now active.', 'type' => 'success']);
    }

    public function unmatchedPayments(Request $request)
    {
        $title = "Unmatched Payments | " . config('global.site_name');
        $page_title = "Unmatched Payments";

        $payments = UnmatchedPayment::where('status', 'unresolved')
            ->orderBy('paid_at', 'desc')
            ->paginate(20);

        $boostTypes = BoostType::active()->ordered()->get();
        $boostDurations = BoostDuration::active()->ordered()->get();

        $advertsByPayment = [];
        foreach ($payments as $payment) {
            $user = User::where('email', $payment->customer_email)->first();
            $advertsByPayment[$payment->id] = $user
                ? Advert::where('user_id', $user->user_id)
                    ->where('ad_status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();
        }

        return view('admin.adboost.unmatched-payments', compact(
            'title', 'page_title', 'payments', 'boostTypes', 'boostDurations', 'advertsByPayment'
        ));
    }

    public function completeUnmatchedPayment(Request $request, $id)
    {
        // Note: intentionally not pre-filtered by status='unresolved' here — the
        // atomic claim inside the DB transaction below is the sole source of
        // truth for "already resolved," closing the double-submit race.
        $payment = UnmatchedPayment::findOrFail($id);

        $request->validate([
            'advert_id'      => 'required|integer|exists:adverts,id',
            'boost_type_id'  => 'required|integer|exists:boost_types,id',
            'duration_id'    => 'required|integer|exists:boost_durations,id',
        ]);

        $user = User::where('email', $payment->customer_email)->first();

        if (!$user) {
            return redirect()->back()->with('status', [
                'text' => 'No account found for ' . $payment->customer_email . ' — cannot complete.',
                'type' => 'danger',
            ]);
        }

        $advert = Advert::where('id', $request->advert_id)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return redirect()->back()->with('status', [
                'text' => 'That advert does not belong to ' . $payment->customer_email . ' — cannot complete.',
                'type' => 'danger',
            ]);
        }

        $boostType = BoostType::findOrFail($request->boost_type_id);
        $duration  = BoostDuration::findOrFail($request->duration_id);
        $amount    = $boostType->calculatePrice($duration->days, $duration->discount_percentage);

        $amountPaid     = $payment->amount; // Flutterwave amounts are stored in major currency units, not kobo
        $amountMismatch = abs($amount - $amountPaid) > max(5, $amountPaid * 0.02);

        $boost = null;

        try {
            DB::transaction(function () use ($payment, $advert, $boostType, $duration, $amount, &$boost) {
                $claimed = UnmatchedPayment::where('id', $payment->id)
                    ->where('status', 'unresolved')
                    ->update([
                        'status'      => 'resolved',
                        'resolved_by' => optional(AdminHelper::currentAdmin())->username,
                        'resolved_at' => Carbon::now(),
                    ]);

                if (!$claimed) {
                    throw new \RuntimeException('unmatched_payment_already_resolved');
                }

                $boost = AdvertBoost::create([
                    'advert_id'         => $advert->id,
                    'user_id'           => $advert->user_id,
                    'trans_id'          => $payment->transaction_id,
                    'payment_reference' => $payment->reference,
                    'amount'            => $amount,
                    'boost_type'        => strtolower($boostType->name),
                    'duration'          => $duration->days,
                    'boost_type_id'     => $boostType->id,
                    'duration_id'       => $duration->id,
                    'start_date'        => Carbon::now(),
                    'payment_status'    => 'paid',
                    'boost_status'      => 'active',
                    'upload_proof'      => 'no',
                ]);

                Advert::where('id', $advert->id)->update(['featured' => 'Yes']);

                $payment->update(['resolved_boost_id' => $boost->id]);
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'unmatched_payment_already_resolved') {
                return redirect()->back()->with('status', [
                    'text' => 'This payment was already resolved by someone else — no duplicate boost was created.',
                    'type' => 'info',
                ]);
            }
            throw $e;
        }

        Log::info('Admin completed unmatched payment', [
            'unmatched_payment_id' => $payment->id,
            'advert_id'            => $advert->id,
        ]);

        if ($amountMismatch) {
            return redirect()->back()->with('status', [
                'text' => 'Boost activated on "' . $advert->ad_title . '", but the computed price (Br ' . number_format($amount, 2) . ') differs from the amount paid (Br ' . number_format($amountPaid, 2) . ') — please double-check this was intentional.',
                'type' => 'warning',
            ]);
        }

        return redirect()->back()->with('status', [
            'text' => 'Payment matched to "' . $advert->ad_title . '" and boost activated.',
            'type' => 'success',
        ]);
    }

}
