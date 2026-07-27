<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            [
                'email' => 'admin@hotloaf.com'
            ],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Admin@123')
            ]
        );

        $admin->assignRole('Admin');
    }
}
