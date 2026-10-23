<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\BuyDirectEscrow;
use App\Services\BuyDirectSettings;
use Illuminate\Http\Request;

/**
 * Admin side of Buy Direct escrow: payouts, refunds (automatic + manual
 * fallback), disputes and the admin-managed escrow settings.
 */
class ManageBuyDirect extends Controller
{
    /* ---------------- Payouts ---------------- */

    public function payout($id)
    {
        [$ok, $message] = BuyDirectEscrow::make()->payout(Payment::findOrFail($id));

        return back()->with('status', ['type' => $ok ? 'success' : 'danger', 'text' => $message]);
    }

    public function markPaidOut(Request $request, $id)
    {
        $request->validate(['note' => 'required|string|max:250']);

        [$ok, $message] = BuyDirectEscrow::make()->markPaidOutManually(Payment::findOrFail($id), $request->input('note'));

        return back()->with('status', ['type' => $ok ? 'success' : 'danger', 'text' => $message]);
    }

    /* ---------------- Refunds ---------------- */

    public function refunds(Request $request)
    {
        $filter = $request->get('filter', 'attention');

        $query = Payment::with(['advert.owner', 'user'])
            ->where('payment_status', 'paid')
            ->where(fn ($q) => $q->where('buyer_status', 'canceled')->orWhere('seller_status', 'canceled'));

        if ($filter === 'attention') {
            $query->whereIn('refund_status', ['none', 'failed', 'processing']);
        } elseif ($filter === 'refunded') {
            $query->where('refund_status', 'refunded');
        }

        return view('admin.buy-direct.refunds', [
            'title'      => 'Buy Direct Refunds | ' . config('global.site_name'),
            'page_title' => 'Buy Direct Refunds',
            'payments'   => $query->orderByDesc('canceled_at')->orderByDesc('id')->paginate(20)->withQueryString(),
            'filter'     => $filter,
        ]);
    }

    public function refund($id)
    {
        [$ok, $message] = BuyDirectEscrow::make()->refund(Payment::findOrFail($id));

        return back()->with('status', ['type' => $ok ? 'success' : 'danger', 'text' => $message]);
    }

    public function markRefunded(Request $request, $id)
    {
        $request->validate(['note' => 'required|string|max:200']);

        [$ok, $message] = BuyDirectEscrow::make()->markRefundedManually(Payment::findOrFail($id), $request->input('note'));

        return back()->with('status', ['type' => $ok ? 'success' : 'danger', 'text' => $message]);
    }

    /** Admin cancels a paid order (e.g. at the buyer's request after shipping). */
    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:250']);

        [$ok, $message] = BuyDirectEscrow::make()->cancel(Payment::findOrFail($id), 'admin', $request->input('reason'));

        return back()->with('status', ['type' => $ok ? 'success' : 'danger', 'text' => $ok ? 'Order canceled. ' . (BuyDirectSettings::autoRefund() ? 'Refund started.' : 'Refund it from Buy Direct Refunds.') : $message]);
    }

    /* ---------------- Disputes ---------------- */

    public function disputes(Request $request)
    {
        $status = $request->get('status', 'open') === 'resolved' ? 'resolved' : 'open';

        return view('admin.buy-direct.disputes', [
            'title'      => 'Buy Direct Disputes | ' . config('global.site_name'),
            'page_title' => 'Buy Direct Disputes',
            'payments'   => Payment::with(['advert.owner', 'user'])
                ->where('dispute_status', $status)
                ->orderByDesc($status === 'open' ? 'dispute_opened_at' : 'dispute_resolved_at')
                ->paginate(20)->withQueryString(),
            'status'     => $status,
        ]);
    }

    public function resolveDispute(Request $request, $id)
    {
        $data = $request->validate([
            'resolution' => 'required|in:refund,release',
            'note'       => 'required|string|max:1000',
        ]);

        [$ok, $message] = BuyDirectEscrow::make()->resolveDispute(Payment::findOrFail($id), $data['resolution'], $data['note']);

        return back()->with('status', ['type' => $ok ? 'success' : 'danger', 'text' => $ok ? 'Dispute resolved. ' . $message : $message]);
    }

    /* ---------------- Settings ---------------- */

    public function settings(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'buy_direct_ship_deadline_days'   => 'required|integer|min:1|max:30',
                'buy_direct_auto_release_days'    => 'required|integer|min:1|max:60',
                'buy_direct_commission_rate'      => 'required|numeric|min:0|max:50',
                'buy_direct_commission_rate_high' => 'required|numeric|min:0|max:50',
                'buy_direct_commission_threshold' => 'required|numeric|min:0',
                'buy_direct_auto_payout'          => 'nullable|boolean',
                'buy_direct_auto_refund'          => 'nullable|boolean',
            ]);

            $data['buy_direct_auto_payout'] = $request->boolean('buy_direct_auto_payout') ? 1 : 0;
            $data['buy_direct_auto_refund'] = $request->boolean('buy_direct_auto_refund') ? 1 : 0;

            BuyDirectSettings::set($data);

            return redirect('/settings/buy-direct')->with('status', ['type' => 'success', 'text' => 'Buy Direct settings saved. New deadlines apply to orders paid or shipped from now on.']);
        }

        return view('admin.settings.buy-direct', [
            'title'    => 'Buy Direct Settings | ' . config('global.site_name'),
            'settings' => BuyDirectSettings::all(),
        ]);
    }
}
