<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * All application permissions grouped by domain.
     */
    private array $permissions = [
        // User management
        'manage-users',
        'view-users',

        // Product management
        'manage-products',
        'view-products',

        // Order management
        'manage-orders',
        'view-orders',
        'create-orders',

        // Inventory management
        'manage-inventory',
        'view-inventory',

        // Campaign management
        'manage-campaigns',
        'view-campaigns',

        // Notification management
        'manage-notifications',

        // Analytics
        'view-analytics',

        // Settings
        'manage-settings',
    ];

    /**
     * Role → permissions mapping.
     */
    private array $rolePermissions = [
        'admin' => [
            'manage-users',
            'view-users',
            'manage-products',
            'view-products',
            'manage-orders',
            'view-orders',
            'create-orders',
            'manage-inventory',
            'view-inventory',
            'manage-campaigns',
            'view-campaigns',
            'manage-notifications',
            'view-analytics',
            'manage-settings',
        ],
        'field_agent' => [
            'view-products',
            'view-orders',
            'create-orders',
            'view-inventory',
            'view-campaigns',
        ],
        'delivery' => [
            'view-orders',
        ],
        'customer' => [
            'view-products',
            'create-orders',
            'view-campaigns',
        ],
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        foreach ($this->rolePermissions as $roleName => $rolePerms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePerms);
        }

        $this->command->info('✅ Roles and permissions seeded successfully.');
    }
}
