<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@nisaticaret.com');
        $phone      = env('ADMIN_PHONE', '+905000000000');

        // Never set firebase_uid here — FirebaseLoginUseCase finds the admin by
        // phone and sets it on first login, avoiding unique-constraint races.
        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name'              => env('ADMIN_NAME', 'Admin User'),
                'phone'             => $phone,
                'role'              => 'admin',
                'is_active'         => true,
                'password'          => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ]
        );

        // Ensure profile exists
        $admin->profile()->firstOrCreate(['user_id' => $admin->id]);

        // Assign admin role (Spatie)
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info("✅ Admin: {$admin->email} | phone: {$admin->phone}");
    }
}
