<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SisterSite;
use Illuminate\Http\Request;

class AdminSisterSiteController extends Controller
{
    /**
     * Display a listing of sister sites.
     */
    public function index(Request $request)
    {
        $title = "Manage Sister Sites - " . config('global.site_name');
        $sisterSites = SisterSite::ordered()->get();

        return view('admin.settings.sister-sites.index', compact('title', 'sisterSites'));
    }

    /**
     * Store a newly created sister site.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'flag' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        $validated['flag'] = $this->storeFlag($request);

        $sisterSite = SisterSite::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sister site created successfully',
            'sisterSite' => $sisterSite,
        ]);
    }

    /**
     * Update the specified sister site.
     */
    public function update(Request $request, $id)
    {
        $sisterSite = SisterSite::findOrFail($id);

        $validated = $request->validate([
            'country_name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'flag' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('flag')) {
            $validated['flag'] = $this->storeFlag($request);
        } else {
            unset($validated['flag']);
        }

        $sisterSite->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sister site updated successfully',
            'sisterSite' => $sisterSite,
        ]);
    }

    /**
     * Remove the specified sister site.
     */
    public function destroy($id)
    {
        $sisterSite = SisterSite::findOrFail($id);
        $sisterSite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sister site deleted successfully',
        ]);
    }

    /**
     * Toggle sister site active status.
     */
    public function toggleStatus($id)
    {
        $sisterSite = SisterSite::findOrFail($id);
        $sisterSite->is_active = !$sisterSite->is_active;
        $sisterSite->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'is_active' => $sisterSite->is_active,
        ]);
    }

    /**
     * Move the uploaded flag image into public/uploads/sister-sites and return its relative path.
     */
    private function storeFlag(Request $request): string
    {
        $image = $request->file('flag');
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads/sister-sites'), $imageName);

        return 'uploads/sister-sites/' . $imageName;
    }
}
