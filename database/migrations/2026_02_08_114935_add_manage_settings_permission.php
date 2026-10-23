<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up()
    {
        // Create the permission if it doesn't exist
        $permission = Permission::firstOrCreate(
            ['name' => 'manage_settings'],
            ['guard_name' => 'admin']
        );

        // Assign to super_admin role
        $superAdmin = Role::where('name', 'super_admin')
            ->where('guard_name', 'admin')
            ->first();

        if ($superAdmin && !$superAdmin->hasPermissionTo('manage_settings', 'admin')) {
            $superAdmin->givePermissionTo($permission);
        }

        // Assign to SEO_Manager role
        $seoManager = Role::where('name', 'SEO_Manager')
            ->where('guard_name', 'admin')
            ->first();

        if ($seoManager && !$seoManager->hasPermissionTo('manage_settings', 'admin')) {
            $seoManager->givePermissionTo($permission);
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down()
    {
        // Remove permission from roles
        $permission = Permission::where('name', 'manage_settings')
            ->where('guard_name', 'admin')
            ->first();

        if ($permission) {
            $superAdmin = Role::where('name', 'super_admin')
                ->where('guard_name', 'admin')
                ->first();

            $seoManager = Role::where('name', 'SEO_Manager')
                ->where('guard_name', 'admin')
                ->first();

            if ($superAdmin) {
                $superAdmin->revokePermissionTo($permission);
            }

            if ($seoManager) {
                $seoManager->revokePermissionTo($permission);
            }

            // Delete the permission
            $permission->delete();
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
