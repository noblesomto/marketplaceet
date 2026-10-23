<?php

namespace App\Http\Controllers\Shop;
use App\Http\Controllers\Controller;

use App\Models\GigLogistic;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Models\Shipping;
use Carbon\Carbon;

class ShipperController extends Controller
{
    public function index(Request $request)
    {
        $title = "Shipper Portal — " . config('global.site_name');
        return view('shipper.index', compact('title'));
    }

    public function get_shipping(Request $request)
    {
        $request->validate([
            'ship_code' => 'required|size:10|alpha_num',
        ], [
            'ship_code.required'  => 'Please enter the 10-digit shipping code.',
            'ship_code.size'      => 'The shipping code must be exactly 10 characters.',
            'ship_code.alpha_num' => 'The shipping code must contain only letters and numbers.',
        ]);

        $ship_code = strtoupper(trim($request->ship_code));
        $ship = Payment::where('ship_code', $ship_code)->where('payment_status', 'paid')->first();

        if ($ship) {
            return redirect()->route('shipper.order.details', $ship_code);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'No order found for that shipping code. Please check and try again.');
    }

    public function order_details(Request $request, $id)
    {
        $title = "Order Details — " . config('global.site_name');

        $ship = Payment::with([
            'advert.media',
            'advert.user',
            'user',
            'shipping',
            'cityLocation.state',
        ])->where('ship_code', $id)->where('payment_status', 'paid')->first();

        if (!$ship) {
            return redirect()->route('shipper.index')
                ->with('error', 'No paid order found for shipping code "' . $id . '". Please check and try again.');
        }

        return view('shipper.order-details', compact('title', 'ship'));
    }

    public function update_shipping(Request $request, $id)
    {
        $request->validate([
            'tracking_id'     => 'required|string|max:100',
            'shipping_status' => 'required|in:pending,shipped,pickup,delivered,canceled',
        ], [
            'tracking_id.required'     => 'Please enter a tracking ID.',
            'shipping_status.required' => 'Please select a shipping status.',
            'shipping_status.in'       => 'Invalid shipping status selected.',
        ]);

        $ship = Payment::with([
            'advert.user',
            'user',
            'shipping',
            'cityLocation.state',
        ])->where('ship_code', $id)->where('payment_status', 'paid')->firstOrFail();

        DB::table('payments')
            ->where('ship_code', $id)
            ->update([
                'tracking_id'          => $request->input('tracking_id'),
                'shipping_status'      => $request->input('shipping_status'),
                'updated_at'           => Carbon::now(),
                'shipping_status_date' => Carbon::now(),
            ]);

        $status = $request->input('shipping_status');

        // Reload the payment so the notifier has fresh data
        $ship->refresh();

        \App\Services\ShippingStatusNotifier::notify($ship, $status);

        return redirect()->back()->with('success', 'Shipping status updated successfully.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('ship_id');
        return redirect()->route('shipper.login')->with('success', 'You have been logged out.');
    }
}
