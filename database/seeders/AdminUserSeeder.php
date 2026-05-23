<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@nisaticaret.com'],
            [
                'name'              => 'Admin User',
                'phone'             => '+905000000000',
                'role'              => 'admin',
                'is_active'         => true,
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Ensure profile exists
        $admin->profile()->firstOrCreate(['user_id' => $admin->id]);

        // Assign admin role (Spatie)
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info("✅ Admin user: {$admin->email} | Password: password");
    }
}
