<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManageAdminUsers extends Controller
{
    public function index(Request $request)
    {
        $title = "Manage Admins - " . config('global.site_name');
        $admins = Admin::get();

        return view('admin.settings.admins.users', compact('title','admins'));

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:admins,username',
            'email'    => 'required|email|max:255|unique:admins,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $admin = Admin::create([
            'admin_id' => rand(11111,99999),
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'admin'   => $admin,
        ]);
    }


    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        //dd($id);
        $validated = $request->validate([
            'username' => [
                'required', 'string', 'max:255',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'email'    => [
                'required', 'email', 'max:255',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'password' => 'nullable|min:6|confirmed',
            'status' => 'required',
        ]);

        $admin->username = $validated['username'];
        $admin->email    = $validated['email'];
        $admin->status    = $validated['status'];

        if (!empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->save();

        return response()->json([
            'success' => true,
            'admin'   => $admin,
        ]);
    }

    public function reset2fa($id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);
        }

        // Prevent resetting your own 2FA via this panel
        if ($admin->id === auth()->guard('admin')->id()) {
            return response()->json(['success' => false, 'message' => 'You cannot reset your own 2FA from here.'], 403);
        }

        $admin->update([
            'google2fa_secret'        => null,
            'two_factor_confirmed_at' => null,
        ]);

        return response()->json(['success' => true, 'message' => '2FA has been reset. The admin will be prompted to set it up on next login.']);
    }

    /**
     * Delete an admin.
     */
    public function destroy($id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found',
            ], 404);
        }

        $admin->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
