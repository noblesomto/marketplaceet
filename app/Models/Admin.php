<?php
// app/Models/Admin.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Change from Model
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable // Change from Model
{
    use HasFactory, HasRoles;

    protected $guard_name = 'admin'; // Specify admin guard

    protected $fillable = ['admin_id', 'username', 'remember_token', 'email', 'password', 'google2fa_secret', 'two_factor_confirmed_at'];

    protected $hidden = ['password', 'remember_token', 'google2fa_secret'];

    // Keep your old methods for backward compatibility during transition
    public function oldRoles()
    {
        return $this->belongsToMany(Role::class, 'admin_roles');
    }

    public function hasOldRole($role)
    {
        return $this->oldRoles->contains('name', $role);
    }

    public function hasOldPermission($permission)
    {
        foreach ($this->oldRoles as $role) {
            if ($role->permissions->contains('name', $permission)) {
                return true;
            }
        }
        return false;
    }

    public function isSuperAdmin()
    {
        // Option A: always treat the very first admin as super admin
        if ($this->id === 1) {
            return true;
        }
        // Option B: check if the admin has explicit super_admin role
        return $this->hasRole('super_admin');
    }
}
