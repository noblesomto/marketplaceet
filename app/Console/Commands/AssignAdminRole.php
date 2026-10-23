<?php
// app/Console/Commands/AssignAdminRole.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use Spatie\Permission\Models\Role;

class AssignAdminRole extends Command
{
    protected $signature = 'admin:assign-role {admin_id} {role}';
    protected $description = 'Assign role to admin user';

    public function handle()
    {
        $adminId = $this->argument('admin_id');
        $roleName = $this->argument('role');

        $admin = Admin::find($adminId);

        if (!$admin) {
            $this->error("❌ Admin with ID {$adminId} not found!");
            return 1;
        }

        $availableRoles = ['super_admin', 'Advert_manager', 'Customer_care', 'Resolution'];

        if (!in_array($roleName, $availableRoles)) {
            $this->error("❌ Role '{$roleName}' does not exist!");
            $this->line("Available roles: " . implode(', ', $availableRoles));
            return 1;
        }

        // Check if already has this role
        if ($admin->hasRole($roleName)) {
            $this->warn("⚠️  {$admin->username} already has role '{$roleName}'");
            return 0;
        }

        $admin->assignRole($roleName);
        $this->info("✅ Successfully assigned '{$roleName}' to {$admin->username}");

        // Show permissions
        $permissions = $admin->getAllPermissions()->pluck('name');
        $this->line("Permissions: " . $permissions->implode(', '));

        return 0;
    }
}
