<?php

namespace App\Traits;

use App\Models\User;

trait HasUserSession
{
    /**
     * Get the authenticated user from session
     *
     * @return User|null
     */
    protected function getUserFromSession()
    {
        $userId = session('user_id');
        if (!$userId) {
            return null;
        }
        return User::find($userId);
    }

    /**
     * Require authenticated user, redirect to login if not found
     *
     * @return User|\Illuminate\Http\RedirectResponse
     */
    protected function requireUser()
    {
        $user = $this->getUserFromSession();
        if (!$user) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return $user;
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    protected function isUserLoggedIn()
    {
        return session()->has('user_id');
    }
}
