<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Permissions
        $permissions = [
            'view_active_adverts',
            'view_disabled_adverts',
            'delete_advert',
            'update_status',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName]);
        }

        // Roles & their permissions
        $roles = [
            'customer_care' => [
                'view_active_adverts',
                'view_disabled_adverts',
            ],
            'advert_manager' => [
                'view_active_adverts',
                'view_disabled_adverts',
                'delete_advert',
            ],
            'resolution' => [
                'update_status',
            ],
            'super_admin' => [
                'view_active_adverts',
                'view_disabled_adverts',
                'delete_advert',
                'update_status',
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $permissionModels = Permission::whereIn('name', $perms)->get();
            $role->permissions()->sync($permissionModels->pluck('id'));
        }
    }
}
