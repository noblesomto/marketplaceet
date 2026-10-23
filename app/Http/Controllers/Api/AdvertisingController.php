<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertising;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvertisingController extends Controller
{
    /**
     * Get active advertisements for mobile display.
     *
     * Returns all active advertisements grouped by type (banner/sidebar).
     * Only returns ads where status is 'active' and duration has not expired.
     *
     * @group Advertising
     *
     * @queryParam type string Filter by type: banner or sidebar. Example: banner
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "banner": [
     *       {
     *         "id": 1,
     *         "advert_id": 12345,
     *         "company": "Acme Ltd",
     *         "image_url": "https://marketplace.et/uploads/advertising/ad.jpg",
     *         "url": "https://acme.com",
     *         "type": "banner",
     *         "start_date": "2026-01-01T00:00:00.000000Z",
     *         "expires_at": "2026-03-01T00:00:00.000000Z",
     *         "days_remaining": 12
     *       }
     *     ],
     *     "sidebar": []
     *   },
     *   "total": 1
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type');

        $query = Advertising::where('status', 'active')
            ->whereNotNull('start_date')
            ->where(function ($q) {
                $q->whereRaw('DATE_ADD(start_date, INTERVAL duration DAY) >= CURDATE()');
            })
            ->orderBy('created_at', 'desc');

        if ($type && in_array($type, ['banner', 'sidebar'])) {
            $query->where('type', $type);
        }

        $ads = $query->get()->map(function ($ad) {
            $expiresAt = Carbon::parse($ad->start_date)->addDays((int) $ad->duration);

            $desktopImageUrl = $ad->image
                ? url('uploads/advertising/' . $ad->image)
                : null;

            $mobileImageUrl = $ad->mobile_image
                ? url('uploads/advertising/' . $ad->mobile_image)
                : $desktopImageUrl; // falls back to desktop, or null if neither set

            return [
                'id'                 => $ad->id,
                'advert_id'          => $ad->advert_id,
                'company'            => $ad->company,
                'image_url'          => $mobileImageUrl,
                'desktop_image_url'  => $desktopImageUrl,
                'url'                => $ad->url,
                'type'               => $ad->type,
                'start_date'         => $ad->start_date,
                'expires_at'         => $expiresAt->toDateTimeString(),
                'days_remaining'     => max(0, (int) now()->diffInDays($expiresAt, false)),
            ];
        });

        if ($type && in_array($type, ['banner', 'sidebar'])) {
            return response()->json([
                'success' => true,
                'data'    => $ads->values(),
                'total'   => $ads->count(),
            ]);
        }

        // Group by type when no type filter applied
        $grouped = [
            'banner'  => $ads->where('type', 'banner')->values(),
            'sidebar' => $ads->where('type', 'sidebar')->values(),
        ];

        return response()->json([
            'success' => true,
            'data'    => $grouped,
            'total'   => $ads->count(),
        ]);
    }

    /**
     * Track an advertisement click.
     *
     * Records a click event when a user taps an advertisement in the mobile app.
     * The redirect URL is returned so the app can open it in a browser.
     *
     * @group Advertising
     *
     * @urlParam id integer required The advert_id of the advertisement. Example: 12345
     *
     * @response 200 {
     *   "success": true,
     *   "url": "https://acme.com",
     *   "company": "Acme Ltd"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Advertisement not found"
     * }
     */
    public function click($id): JsonResponse
    {
        $ad = Advertising::where('advert_id', $id)
            ->where('status', 'active')
            ->first();

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'Advertisement not found',
            ], 404);
        }

        // Future: increment click_count column here

        return response()->json([
            'success' => true,
            'url'     => $ad->url,
            'company' => $ad->company,
        ]);
    }
}
