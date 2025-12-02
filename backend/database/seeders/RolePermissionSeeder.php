<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        $permissions = [
            // User management
            'create-users',
            'edit-users', 
            'delete-users',
            'view-users',
            
            // Role management
            'create-roles',
            'edit-roles',
            'delete-roles',
            'view-roles',
            'assign-roles',
            
            // Contract management
            'create-contracts',
            'edit-contracts',
            'delete-contracts',
            'view-contracts',
            
            // Passport management
            'create-passports',
            'edit-passports',
            'delete-passports',
            'view-passports',
            
            // Card management
            'create-cards',
            'edit-cards',
            'delete-cards',
            'view-cards',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $employee = Role::firstOrCreate(['name' => 'employee']);

        // Assign permissions to roles
        $superAdmin->givePermissionTo(Permission::all());
        
        $admin->givePermissionTo([
            'view-users', 'edit-users', 'assign-roles',
            'create-contracts', 'edit-contracts', 'delete-contracts', 'view-contracts',
            'create-passports', 'edit-passports', 'delete-passports', 'view-passports',
            'create-cards', 'edit-cards', 'delete-cards', 'view-cards',
        ]);
        
        $employee->givePermissionTo([
            'view-contracts', 'create-contracts', 'edit-contracts',
            'view-passports', 'create-passports', 'edit-passports',
            'view-cards', 'create-cards', 'edit-cards',
        ]);

        // Create super admin user if doesn't exist
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@redwan.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password123'),
            ]
        );
        
        $superAdminUser->assignRole('super-admin');
    }
}
