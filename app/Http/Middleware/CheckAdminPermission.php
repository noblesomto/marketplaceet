<?php
// app/Http/Middleware/CheckAdminPermission.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\AdminHelper;

class CheckAdminPermission
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $admin = AdminHelper::currentAdmin();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please login first');
        }

        // Super admin bypasses all permission checks
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Check if admin has any of the required permissions
        if (!$admin->hasAnyPermission($permissions)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
