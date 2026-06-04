<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $phone       = env('ADMIN_PHONE', '+905000000000');
        $firebaseUid = env('ADMIN_FIREBASE_UID') ?: null;
        $adminEmail  = env('ADMIN_EMAIL', 'admin@nisaticaret.com');

        // If a spurious user owns this firebase_uid, detach it so the UPDATE below won't hit the unique constraint
        if ($firebaseUid) {
            User::where('firebase_uid', $firebaseUid)
                ->where('email', '!=', $adminEmail)
                ->update(['firebase_uid' => null]);
        }

        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            array_filter([
                'name'              => env('ADMIN_NAME', 'Admin User'),
                'phone'             => $phone,
                'role'              => 'admin',
                'is_active'         => true,
                'password'          => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'email_verified_at' => now(),
                'firebase_uid'      => $firebaseUid,
            ], fn ($v) => $v !== null)
        );

        // Ensure profile exists
        $admin->profile()->firstOrCreate(['user_id' => $admin->id]);

        // Assign admin role (Spatie)
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info("✅ Admin: {$admin->email} | phone: {$admin->phone}" . ($firebaseUid ? " | firebase_uid set" : ""));
    }
}
