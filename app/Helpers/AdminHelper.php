<?php
// app/Helpers/AdminHelper.php
namespace App\Helpers;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class AdminHelper
{
    /**
     * Get the currently logged-in admin
     *
     * @return Admin|null
     */
    public static function currentAdmin()
    {
        return Auth::guard('admin')->user();
    }

    /**
     * Check if an admin is logged in
     *
     * @return bool
     */
    public static function check()
    {
        return Auth::guard('admin')->check();
    }

    /**
     * Check if current admin has permission
     *
     * @param string|array $permission
     * @return bool
     */
    public static function can($permission)
    {
        $admin = self::currentAdmin();

        if (!$admin) {
            return false;
        }

        // Super admin can do everything
        if ($admin->isSuperAdmin()) {
            return true;
        }

        // Check permission using Spatie
        if (is_array($permission)) {
            return $admin->hasAnyPermission($permission, 'admin');
        }

        return $admin->hasPermissionTo($permission, 'admin');
    }

    /**
     * Check if current admin has role
     *
     * @param string|array $role
     * @return bool
     */
    public static function hasRole($role)
    {
        $admin = self::currentAdmin();

        if (!$admin) {
            return false;
        }

        // Super admin always returns true
        if ($admin->isSuperAdmin()) {
            return true;
        }

        if (is_array($role)) {
            return $admin->hasAnyRole($role, 'admin');
        }

        return $admin->hasRole($role, 'admin');
    }

    /**
     * Get admin ID
     *
     * @return int|null
     */
    public static function id()
    {
        return Auth::guard('admin')->id();
    }
}
