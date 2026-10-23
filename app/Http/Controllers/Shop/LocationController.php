<?php

namespace App\Http\Controllers\Shop;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\State;
use App\Models\GigLogistic;


class LocationController extends Controller
{
     public function index()
    {
        $states = State::all();
        return view('location.select', compact('states'));
    }

    public function getGIG($state_id)
    {
        $cities = GigLogistic::where('state_id', $state_id)
        ->select('id', 'city', 'address')
        ->get();
        return response()->json($cities);
    }


    public function getAgilityShippingCost(Request $request)
    {
        // Validate input parameters (kept for backward compatibility with callers)
        $request->validate([
            'sender_station' => 'required|integer',
            'sender_address' => 'required|string',
            'reciever_station' => 'required|integer',
            'reciever_address' => 'required|string',
            'ad_price' => 'required|numeric|min:0',
            'ad_title' => 'required|string|max:255',
            'ad_des' => 'nullable|string|max:4000',
        ]);

        // Automated courier shipping-cost calculation (previously via Agility,
        // Nigeria-only) is not available for Ethiopia yet. No cost is added;
        // buyer and seller arrange delivery directly.
        return response()->json([
            'status' => true,
            'data' => ['GrandTotal' => 0],
        ]);
    }

}
