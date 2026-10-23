<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure the authenticated user has required profile fields filled in.
 *
 * Usage on a route:  ->middleware('profile.complete:phone')
 * Multiple fields:   ->middleware('profile.complete:phone,address')
 *
 * Easily extensible — add any column name as a parameter and it will be
 * checked automatically. The user is redirected to the profile update page
 * with a flash message telling them exactly what to complete.
 */
class RequireProfileComplete
{
    public function handle(Request $request, Closure $next, string ...$fields): Response
    {
        $userId = $request->session()->get('user_id');

        if (!$userId) {
            return $next($request);
        }

        $user = DB::table('users')->where('user_id', $userId)->first();

        if (!$user) {
            return $next($request);
        }

        foreach ($fields as $field) {
            if (empty($user->{$field})) {
                // Store intended URL so we can redirect back after the update
                if ($request->isMethod('GET')) {
                    $request->session()->put('url.intended', $request->fullUrl());
                }

                $label = match($field) {
                    'phone'   => 'phone number',
                    'address' => 'address',
                    'name'    => 'full name',
                    default   => $field,
                };

                return redirect()->route('user.profile.update')
                    ->with('profile_required', "Please add your {$label} before you can continue.");
            }
        }

        return $next($request);
    }
}
