<?php
// database/seeders/MigrateToSpatiePermissionSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;

class MigrateToSpatiePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info("Creating permissions...");
        $this->createPermissions();

        $this->command->info("Creating roles...");
        $this->createSuperAdminRole();
        $this->createAdvertManagerRole();
        $this->createCustomerCareRole();
        $this->createResolutionRole();
        $this->createSEOManagerRole();

        $this->command->info("Assigning roles to existing admins...");
        $this->assignRolesToAdmins();

        $this->command->info("✅ Migration completed successfully!");
    }

    private function createPermissions()
    {
        $permissions = [
            // Advert Management
            'create_advert',
            'update_advert',
            'delete_advert',
            'view_adverts',
            'manage_advert_status',
            'manage_sold_status',

            // Boost Management
            'view_active_boosts',
            'view_completed_boosts',
            'view_unpaid_boosts',
            'manage_boost_status',
            'manage_boost_payment',

            // Category Management
            'manage_categories',
            'manage_subcategories',
            'manage_brands',
            'manage_models',

            // User Management
            'view_users',
            'manage_user_status',
            'verify_users',
            'search_users',

            // Resolution
            'view_reports',
            'manage_report_status',

            // Settings & SEO
            'manage_shipping',
            'manage_settings',
            'manage_seo',
            'view_analytics',

            // Payments & Blog
            'view_payments',
            'manage_payments',
            'view_settlements',
            'manage_settlements',
            'view_blog',
            'create_blog',
            'edit_blog',
            'delete_blog',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
        }

        $this->command->info("✅ Created " . count($permissions) . " permissions");
    }

    private function createSuperAdminRole()
    {
        $superAdmin = SpatieRole::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'admin'
        ]);
        $superAdmin->syncPermissions(Permission::all());

        $this->command->info("✅ Super Admin role created with all permissions");
    }

    private function createAdvertManagerRole()
    {
        $advertManager = SpatieRole::firstOrCreate([
            'name' => 'Advert_manager',
            'guard_name' => 'admin'
        ]);

        $advertManager->syncPermissions([
            'create_advert',
            'update_advert',
            'delete_advert',
            'view_adverts',
            'manage_advert_status',
            'manage_sold_status',
            'view_active_boosts',
            'view_completed_boosts',
            'view_unpaid_boosts',
            'manage_boost_status',
            'manage_boost_payment',
            'manage_categories',
            'manage_subcategories',
            'manage_brands',
            'manage_models',
        ]);

        $this->command->info("✅ Advert Manager role created");
    }

    private function createCustomerCareRole()
    {
        $customerCare = SpatieRole::firstOrCreate([
            'name' => 'Customer_care',
            'guard_name' => 'admin'
        ]);

        // Has all Advert Manager permissions PLUS user management
        $customerCare->syncPermissions([
            // Advert Management
            'create_advert',
            'update_advert',
            'delete_advert',
            'view_adverts',
            'manage_advert_status',
            'manage_sold_status',
            'view_active_boosts',
            'view_completed_boosts',
            'view_unpaid_boosts',
            'manage_boost_status',
            'manage_boost_payment',
            'manage_categories',
            'manage_subcategories',
            'manage_brands',
            'manage_models',
            // User Management (additional)
            'view_users',
            'manage_user_status',
            'verify_users',
            'search_users',
            'manage_shipping',
        ]);

        $this->command->info("✅ Customer Care role created");
    }

    private function createResolutionRole()
    {
        $resolution = SpatieRole::firstOrCreate([
            'name' => 'Resolution',
            'guard_name' => 'admin'
        ]);

        $resolution->syncPermissions([
            'view_reports',
            'manage_report_status',
        ]);

        $this->command->info("✅ Resolution role created");
    }

    private function createSEOManagerRole()
    {
        $seoManager = SpatieRole::firstOrCreate([
            'name' => 'SEO_Manager',
            'guard_name' => 'admin'
        ]);

        $seoManager->syncPermissions([
            'manage_seo',
            'view_analytics',
            'manage_settings',
        ]);

        $this->command->info("✅ SEO Manager role created");
    }

    private function assignRolesToAdmins()
    {
        $admins = Admin::all();

        if ($admins->isEmpty()) {
            $this->command->warn("⚠️  No admins found in database");
            return;
        }

        $this->command->info("Found {$admins->count()} admin(s)");

        foreach ($admins as $admin) {
            // Admin ID 1 is always super admin
            if ($admin->id === 1) {
                $admin->syncRoles(['super_admin']);
                $this->command->info("✅ Assigned super_admin to {$admin->username} (ID: {$admin->id})");
                continue;
            }

            // If your admins table has a 'role' or 'role_name' column
            if (isset($admin->role) && !empty($admin->role)) {
                try {
                    $admin->syncRoles([$admin->role]);
                    $this->command->info("✅ Assigned {$admin->role} to {$admin->username}");
                } catch (\Exception $e) {
                    $this->command->warn("⚠️  Could not assign role '{$admin->role}' to {$admin->username}");
                }
                continue;
            }

            // Default: prompt for manual assignment
            $this->command->warn("⚠️  Admin {$admin->username} (ID: {$admin->id}, Email: {$admin->email}) needs role assignment");
            $this->command->line("   Available roles: super_admin, Advert_manager, Customer_care, Resolution, SEO_Manager");
        }
    }

    private function assignRoleByEmail($email, $role)
    {
        $admin = Admin::where('email', $email)->first();
        if ($admin) {
            $admin->syncRoles([$role]);
            $this->command->info("✅ Assigned {$role} to {$admin->username} ({$email})");
        } else {
            $this->command->warn("⚠️  Admin with email {$email} not found");
        }
    }
}
