<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BlockedUser;
use App\Traits\HasUserSession;

class BlockUser extends Controller
{
    use HasUserSession;
    // BlockController.php
    public function block(Request $request)
    {
        $validated = $request->validate([
            'blocked_id' => 'required|exists:users,user_id',
            'advert_id' => 'nullable|exists:adverts,id',
        ]);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect('/login')->with('error', 'Please login');
        }

        if ($user->user_id == $validated['blocked_id']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You cannot block yourself.'], 400);
            }
            return back()->with('error', 'You cannot block yourself.');
        }

        BlockedUser::firstOrCreate([
            'blocker_id' => $user->user_id,
            'blocked_id' => $validated['blocked_id'],
            'advert_id' => $validated['advert_id'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'User blocked successfully.'], 200);
        }

        return back()->with('success', 'User blocked successfully.');
    }

    public function unblock(Request $request)
    {
        $validated = $request->validate([
            'blocked_id' => 'required|exists:users,user_id',
            'advert_id' => 'nullable|exists:adverts,id',
        ]);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect('/login')->with('error', 'Please login');
        }

        $deleted = BlockedUser::where('blocker_id', $user->user_id)
            ->where('blocked_id', $validated['blocked_id'])
            ->where('advert_id', $validated['advert_id'] ?? null)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'User unblocked successfully.'], 200);
        }

        return back()->with('success', 'User unblocked successfully.');
    }
}
