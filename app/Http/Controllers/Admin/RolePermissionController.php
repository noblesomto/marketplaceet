<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        $title = "Admin Roles - " . config('global.site_name');

        $roles = Role::where('guard_name', 'admin')->with('permissions')->get();
        $permissions = Permission::where('guard_name', 'admin')->get();
        $admins = Admin::with('roles')->get();

        return view('admin.roles.roles', compact('roles','permissions','admins','title'));
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);

        Role::create([
            'name' => $request->name,
            'guard_name' => 'admin',
        ]);

        return back()->with('success', 'Role created successfully!');
    }

    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        Permission::create([
            'name' => $request->name,
            'guard_name' => 'admin',
        ]);

        return back()->with('success', 'Permission created successfully!');
    }

    public function assignPermission(Request $request, $roleId)
    {
        $role = Role::where('guard_name', 'admin')->findOrFail($roleId);

        // Spatie: syncPermissions()
        $role->syncPermissions($request->permissions ?? []);

        return back()->with('success', 'Permissions updated for role: '.$role->name);
    }

    public function assignRoleToAdmin(Request $request, $adminId)
    {
        $admin = Admin::findOrFail($adminId);

        // Spatie: syncRoles()
        $admin->syncRoles($request->roles ?? []);

        return back()->with('success', 'Roles updated for admin: '.$admin->username);
    }
}
