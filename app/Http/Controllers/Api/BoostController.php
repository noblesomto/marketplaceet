<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoostType;
use App\Models\BoostDuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Boost Management
 *
 * APIs for managing advert boosts and pricing
 */
class BoostController extends Controller
{
    /**
     * Get Boost Options
     *
     * Retrieves all available boost types and durations for selection.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "boost_types": [
     *       {
     *         "id": 1,
     *         "name": "Highlight",
     *         "daily_rate": "214.29",
     *         "description": "Highlight your ad with a colored border",
     *         "display_order": 1
     *       }
     *     ],
     *     "durations": [
     *       {
     *         "id": 1,
     *         "days": 7,
     *         "discount_percentage": "0.00",
     *         "label": "7 Days",
     *         "display_order": 1
     *       }
     *     ]
     *   }
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOptions()
    {
        $boostTypes = BoostType::active()
            ->ordered()
            ->select('id', 'name', 'daily_rate', 'description', 'display_order')
            ->get();

        $durations = BoostDuration::active()
            ->ordered()
            ->select('id', 'days', 'discount_percentage', 'label', 'display_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'boost_types' => $boostTypes,
                'durations' => $durations,
            ],
        ]);
    }

    /**
     * Calculate Boost Price
     *
     * Calculates the price for a boost based on type and duration.
     *
     * @bodyParam boost_type_id integer required The ID of the boost type. Example: 1
     * @bodyParam duration_id integer required The ID of the boost duration. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "boost_type": {
     *       "id": 1,
     *       "name": "Highlight",
     *       "daily_rate": "214.29"
     *     },
     *     "duration": {
     *       "id": 1,
     *       "days": 7,
     *       "discount_percentage": "0.00",
     *       "label": "7 Days"
     *     },
     *     "pricing": {
     *       "base_price": "1500.03",
     *       "discount_percentage": "0.00",
     *       "discount_amount": "0.00",
     *       "final_price": "1500.03",
     *       "currency": "ETB"
     *     }
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "errors": {
     *     "boost_type_id": ["The boost type id field is required."]
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Boost type or duration not found"
     * }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculatePrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'boost_type_id' => 'required|integer|exists:boost_types,id',
            'duration_id' => 'required|integer|exists:boost_durations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $boostType = BoostType::find($request->boost_type_id);
        $duration = BoostDuration::find($request->duration_id);

        if (!$boostType || !$duration) {
            return response()->json([
                'success' => false,
                'message' => 'Boost type or duration not found',
            ], 404);
        }

        // Check if both are active
        if (!$boostType->is_active || !$duration->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Selected boost type or duration is currently inactive',
            ], 422);
        }

        // Calculate pricing
        $basePrice = $boostType->calculatePrice($duration->days);
        $discountAmount = ($basePrice * $duration->discount_percentage) / 100;
        $finalPrice = $basePrice - $discountAmount;

        return response()->json([
            'success' => true,
            'data' => [
                'boost_type' => [
                    'id' => $boostType->id,
                    'name' => $boostType->name,
                    'daily_rate' => number_format($boostType->daily_rate, 2, '.', ''),
                    'description' => $boostType->description,
                ],
                'duration' => [
                    'id' => $duration->id,
                    'days' => $duration->days,
                    'discount_percentage' => number_format($duration->discount_percentage, 2, '.', ''),
                    'label' => $duration->label,
                ],
                'pricing' => [
                    'base_price' => number_format($basePrice, 2, '.', ''),
                    'discount_percentage' => number_format($duration->discount_percentage, 2, '.', ''),
                    'discount_amount' => number_format($discountAmount, 2, '.', ''),
                    'final_price' => number_format($finalPrice, 2, '.', ''),
                    'currency' => 'ETB',
                ],
            ],
        ]);
    }
}
