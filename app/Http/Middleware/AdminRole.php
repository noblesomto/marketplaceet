<?php
// app/Http/Middleware/AdminRole.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Please login to access this area.');
        }

        $admin = Auth::guard('admin')->user();

        // Super admin has access to everything
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Check if admin has any of the required roles
        foreach ($roles as $role) {
            if ($admin->hasRole($role, 'admin')) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}
