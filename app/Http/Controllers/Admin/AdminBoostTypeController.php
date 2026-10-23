<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoostType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminBoostTypeController extends Controller
{
    /**
     * Display a listing of boost types.
     */
    public function index(Request $request)
    {
        $title = "Manage Boost Types - " . config('global.site_name');
        $boostTypes = BoostType::ordered()->get();

        return view('admin.settings.boost-types.index', compact('title', 'boostTypes'));
    }

    /**
     * Store a newly created boost type.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:boost_types,name',
            'daily_rate' => 'required|numeric|min:0',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $boostType = BoostType::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Boost type created successfully',
            'boostType' => $boostType,
        ]);
    }

    /**
     * Update the specified boost type.
     */
    public function update(Request $request, $id)
    {
        $boostType = BoostType::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('boost_types')->ignore($boostType->id),
            ],
            'daily_rate' => 'required|numeric|min:0',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $boostType->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Boost type updated successfully',
            'boostType' => $boostType,
        ]);
    }

    /**
     * Remove the specified boost type.
     */
    public function destroy($id)
    {
        $boostType = BoostType::findOrFail($id);

        // Check if there are any active boosts using this type
        $activeBoosts = $boostType->advertBoosts()
            ->where('boost_status', 'active')
            ->count();

        if ($activeBoosts > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete boost type with active boosts. Please deactivate it instead.',
            ], 422);
        }

        $boostType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Boost type deleted successfully',
        ]);
    }

    /**
     * Toggle boost type active status.
     */
    public function toggleStatus($id)
    {
        $boostType = BoostType::findOrFail($id);
        $boostType->is_active = !$boostType->is_active;
        $boostType->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'is_active' => $boostType->is_active,
        ]);
    }
}
