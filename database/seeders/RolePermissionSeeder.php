<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage users',
            'manage categories',
            'manage products',
            'manage inventory',
            'manage suppliers',
            'manage purchases',
            'manage customers',
            'manage orders',
            'manage sales',
            'manage payments',
            'manage deliveries',
            'view reports',
            'print receipts',
            'export reports',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'Admin' => [
                'manage users',
                'manage categories',
                'manage products',
                'manage inventory',
                'manage suppliers',
                'manage purchases',
                'manage customers',
                'manage orders',
                'manage sales',
                'manage payments',
                'manage deliveries',
                'view reports',
                'print receipts',
                'export reports',
            ],

            'Manager' => [
                'manage categories',
                'manage products',
                'manage inventory',
                'manage suppliers',
                'manage purchases',
                'manage customers',
                'manage orders',
                'manage sales',
                'manage payments',
                'manage deliveries',
                'view reports',
                'print receipts',
                'export reports',
            ],

            'Sales Staff' => [
                'manage customers',
                'manage orders',
                'manage sales',
                'manage payments',
                'print receipts',
            ],

            'Inventory Staff' => [
                'manage categories',
                'manage products',
                'manage inventory',
            ],

            'Procurement Staff' => [
                'manage categories',
                'manage products',
                'manage suppliers',
                'manage purchases',
            ],

            'Delivery Staff' => [
                'manage deliveries',
            ],
        ];

        foreach ($roles as $roleName => $permissionNames) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissionNames);
        }
    }
}

