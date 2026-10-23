<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoostDuration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminBoostDurationController extends Controller
{
    /**
     * Display a listing of boost durations.
     */
    public function index(Request $request)
    {
        $title = "Manage Boost Durations - " . config('global.site_name');
        $durations = BoostDuration::ordered()->get();

        return view('admin.settings.boost-durations.index', compact('title', 'durations'));
    }

    /**
     * Store a newly created boost duration.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|unique:boost_durations,days',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'label' => 'required|string|max:255',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $duration = BoostDuration::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Boost duration created successfully',
            'duration' => $duration,
        ]);
    }

    /**
     * Update the specified boost duration.
     */
    public function update(Request $request, $id)
    {
        $duration = BoostDuration::findOrFail($id);

        $validated = $request->validate([
            'days' => [
                'required', 'integer', 'min:1',
                Rule::unique('boost_durations')->ignore($duration->id),
            ],
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'label' => 'required|string|max:255',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $duration->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Boost duration updated successfully',
            'duration' => $duration,
        ]);
    }

    /**
     * Remove the specified boost duration.
     */
    public function destroy($id)
    {
        $duration = BoostDuration::findOrFail($id);

        // Check if there are any active boosts using this duration
        $activeBoosts = $duration->advertBoosts()
            ->where('boost_status', 'active')
            ->count();

        if ($activeBoosts > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete boost duration with active boosts. Please deactivate it instead.',
            ], 422);
        }

        $duration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Boost duration deleted successfully',
        ]);
    }

    /**
     * Toggle boost duration active status.
     */
    public function toggleStatus($id)
    {
        $duration = BoostDuration::findOrFail($id);
        $duration->is_active = !$duration->is_active;
        $duration->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'is_active' => $duration->is_active,
        ]);
    }
}
