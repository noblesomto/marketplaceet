<?php

namespace App\Http\Controllers\Admin;

use App\Services\BuyDirectEscrow;
use App\Services\FlutterwaveGateway;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\User;
use App\Models\Advert;
use Carbon\Carbon;
use Mail;
use App\Mail\PayoutMail;
use App\Mail\BuyDirectMail;
use App\Mail\SellerMail;
use App\Models\GigLogistic;
use App\Services\ShippingStatusNotifier;
use App\Services\SellerCancelNotifier;

class ManagePayments extends Controller
{
    public function completed_payments(Request $request)
    {
        $title = "Completed Payments | " . config('global.site_name');
        $page_title = "Completed Payments";

        $payments = Payment::with([
                'advert.media', // This loads the advert and its firstImage
                'user'
            ])
            ->where("payment_status", "paid")
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        //dd($payments);
        return view('admin.payments.completed-payments', compact('title', 'page_title', 'payments'));
    }

    public function pending_payments(Request $request)
    {
        $title = "Pending Payments | " . config('global.site_name');
        $page_title = "Pending/Failed Payments";

        $payments = Payment::with(['advert.media', 'advert.owner', 'user'])
        ->where("payment_status", '!=', "paid")
        ->whereHas('advert') // Only include payments with an advert
        ->orderBy('created_at', 'desc')
        ->paginate(20);
            //dd($payments);
        return view('admin.payments.pending-payments', compact('title', 'page_title', 'payments'));
    }

    public function update_payment(Request $request, $id)
    {
        $request->validate([
            'buyer_status'    => 'required|in:pending,delivered,canceled',
            'shipping_status' => 'required|in:pending,shipped,pickup,delivered,canceled',
            'seller_status'   => 'required|in:pending,shipped,canceled',
        ]);

        $payment = Payment::with(['advert.user', 'user', 'shipping', 'cityLocation.state'])
            ->where('id', $id)
            ->firstOrFail();

        $escrow = BuyDirectEscrow::make();
        $messages = [];

        // Escrow-affecting changes go through the escrow service so refunds,
        // payouts, deadlines and notifications stay consistent.
        if ($request->input('buyer_status') === 'canceled' || $request->input('seller_status') === 'canceled') {
            if (!$escrow->isCanceled($payment)) {
                $messages[] = $escrow->cancel($payment, 'admin', 'Canceled by admin.')[1];
            }
        } else {
            if ($request->input('seller_status') === 'shipped' && $payment->seller_status !== 'shipped' && $payment->advert) {
                $messages[] = $escrow->markShipped($payment, $payment->advert->user_id)[1];
            }
            if ($request->input('buyer_status') === 'delivered' && $payment->buyer_status !== 'delivered') {
                $messages[] = $escrow->release($payment->fresh(), 'admin')[1];
            }
        }

        // Courier status (legacy orders placed while shipping companies were enabled).
        $newShippingStatus = $request->input('shipping_status');
        if ($newShippingStatus !== ($payment->shipping_status ?? 'pending')) {
            $payment->update(['shipping_status' => $newShippingStatus, 'shipping_status_date' => Carbon::now()]);
            ShippingStatusNotifier::notify($payment, $newShippingStatus);
        }

        return redirect()->back()->with('status', ['text' => trim('Payment Status Updated. ' . implode(' ', array_filter($messages))), 'type' => 'success']);
    }

    /**
     * Manually confirm a pending Buy Direct payment (e.g. the buyer's
     * redirect never came back). Verifies with Flutterwave first.
     */
    public function confirm_payment(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->payment_status === 'paid') {
            return redirect()->back()->with('status', ['text' => 'This payment is already confirmed.', 'type' => 'info']);
        }

        $verified = app(FlutterwaveGateway::class)->verifyByReference((string) $payment->payment_reference);

        if (!$verified['ok'] || $verified['status'] !== 'successful') {
            return redirect()->back()->with('status', ['text' => 'Flutterwave has no successful charge for this order reference (' . ($verified['message'] ?? $verified['status'] ?? 'unknown') . '). Not confirmed.', 'type' => 'danger']);
        }

        $ok = BuyDirectEscrow::make()->markPaid($payment, $verified['data']);

        return redirect()->back()->with('status', $ok
            ? ['text' => 'Payment confirmed. Buyer and seller have been notified.', 'type' => 'success']
            : ['text' => 'Could not confirm: the amount charged does not cover the order total. See the admin email for details.', 'type' => 'danger']);
    }

    public function pending_settlements(Request $request)
    {
        $title = "Pending Settlements | " . config('global.site_name');
        $page_title = "Pending Settlements";

        $payments = Payment::with([
                'advert.firstImage',
                'advert.owner',
                'user'
            ])
            ->where("payment_status", "paid")
            ->where("seller_settlement", "no")
            ->where('buyer_status', '!=', 'canceled')
            ->where('seller_status', '!=', 'canceled')
            // Ready-to-pay and failed payouts first, then in-flight, then still in escrow.
            ->orderByRaw("CASE WHEN buyer_status = 'delivered' AND payout_status IN ('none','failed') AND dispute_status != 'open' THEN 0 WHEN payout_status = 'processing' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.settlements.pending-settlements', compact('title', 'page_title', 'payments'));
    }


    public function completed_settlements(Request $request)
    {
        $title = "Completed Payments | " . config('global.site_name');
        $page_title = "Completed Payments";

        $payments = Payment::with([
                'advert.firstImage',
                'advert.owner',
                'user'
            ])
            ->where("payment_status", "paid")
            ->where("seller_settlement", "yes")
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        //dd($payments);
        return view('admin.settlements.completed-settlements', compact('title', 'page_title', 'payments'));
    }

    /** Legacy endpoint: manual settlement now goes through the escrow fallback. */
    public function confirm_settlement(Request $request, $id)
    {
        return app(ManageBuyDirect::class)->markPaidOut($request->merge(['note' => $request->input('note', 'Paid manually by admin')]), $id);
    }

    /** Legacy endpoint: Flutterwave payout now goes through the escrow service. */
    public function sendPayout(Request $request, $id)
    {
        return app(ManageBuyDirect::class)->payout($id);
    }
}
