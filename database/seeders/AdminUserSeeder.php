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
        // Password is only set on creation; existing password is preserved across restarts.
        $existing = User::where('email', $adminEmail)->first();

        // ADMIN_PHONE may already belong to a different user (e.g. created via
        // Firebase phone login before this seeder ran). Writing it would violate
        // users_phone_unique, so skip the phone until that duplicate is resolved.
        $phoneOwner = User::where('phone', $phone)
            ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))
            ->first();

        if ($phoneOwner) {
            $this->command->warn(
                "⚠️  ADMIN_PHONE {$phone} already belongs to user #{$phoneOwner->id} ({$phoneOwner->email})."
                .' Skipping phone update — delete or re-phone that user, then reseed.'
            );
        }

        if ($existing) {
            $existing->fill(array_filter([
                'name'              => env('ADMIN_NAME', 'Admin User'),
                'phone'             => $phoneOwner ? null : $phone,
                'role'              => 'admin',
                'is_active'         => true,
                'email_verified_at' => now(),
            ], fn ($v) => $v !== null))->save();
            $admin = $existing;
        } elseif ($phoneOwner) {
            // Admin doesn't exist yet and the phone is taken. phone is NOT NULL,
            // so we can't create the admin without one — bail out instead of
            // crashing; resolve the duplicate and reseed.
            $this->command->error('❌ Cannot create admin: ADMIN_PHONE is taken. Skipping.');

            return;
        } else {
            $admin = User::create([
                'email'             => $adminEmail,
                'name'              => env('ADMIN_NAME', 'Admin User'),
                'phone'             => $phone,
                'role'              => 'admin',
                'is_active'         => true,
                'password'          => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ]);
        }

        // Ensure profile exists
        $admin->profile()->firstOrCreate(['user_id' => $admin->id]);

        // Assign admin role (Spatie)
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info("✅ Admin: {$admin->email} | phone: {$admin->phone}");
    }
}
