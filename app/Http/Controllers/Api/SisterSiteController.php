<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SisterSite;

class SisterSiteController extends Controller
{
    /**
     * List active sister (other-country) marketplace sites, for the mobile apps to render as a flag row.
     */
    public function index()
    {
        $sisterSites = SisterSite::activeOrdered()->map(function (SisterSite $site) {
            return [
                'id' => $site->id,
                'country_name' => $site->country_name,
                'flag_url' => asset($site->flag),
                'url' => $site->url,
                'display_order' => $site->display_order,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $sisterSites,
        ]);
    }
}
