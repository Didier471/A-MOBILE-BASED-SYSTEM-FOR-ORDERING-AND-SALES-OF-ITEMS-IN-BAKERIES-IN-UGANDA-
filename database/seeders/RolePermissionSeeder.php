<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage users',
            'manage products',
            'manage categories',
            'manage inventory',
            'manage suppliers',
            'manage customers',
            'manage orders',
            'manage sales',
            'manage payments',
            'manage deliveries',
            'view reports',
            'print receipts',
            'export reports'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web'
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'web'
        ]);

        $supervisor = Role::firstOrCreate([
            'name' => 'Supervisor',
            'guard_name' => 'web'
        ]);

        $cashier = Role::firstOrCreate([
            'name' => 'Cashier',
            'guard_name' => 'web'
        ]);

        $baker = Role::firstOrCreate([
            'name' => 'Baker',
            'guard_name' => 'web'
        ]);

        $customer = Role::firstOrCreate([
            'name' => 'Customer',
            'guard_name' => 'web'
        ]);

        $admin->givePermissionTo(Permission::all());

        $manager->givePermissionTo([
            'manage products',
            'manage categories',
            'manage inventory',
            'manage suppliers',
            'manage customers',
            'manage orders',
            'manage sales',
            'manage payments',
            'manage deliveries',
            'view reports',
            'print receipts',
            'export reports'
        ]);

        $supervisor->givePermissionTo([
            'manage inventory',
            'manage orders',
            'manage deliveries',
            'view reports'
        ]);

        $cashier->givePermissionTo([
            'manage sales',
            'manage payments',
            'print receipts'
        ]);

        $baker->givePermissionTo([
            'manage inventory'
        ]);
    }
}