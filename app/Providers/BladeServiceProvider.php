<?php
// app/Providers/BladeServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\AdminHelper;

class BladeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // @adminCan directive
        Blade::if('adminCan', function ($permission) {
            return AdminHelper::can($permission);
        });

        // @adminRole directive
        Blade::if('adminRole', function ($role) {
            return AdminHelper::hasRole($role);
        });

        // @adminHasAnyRole directive
        Blade::if('adminHasAnyRole', function (...$roles) {
            $admin = AdminHelper::currentAdmin();
            if (!$admin) return false;
            return $admin->hasAnyRole($roles);
        });

        // @adminCanAny directive
        Blade::if('adminCanAny', function (...$permissions) {
            return AdminHelper::can($permissions);
        });
    }

    public function register()
    {
        //
    }
}
