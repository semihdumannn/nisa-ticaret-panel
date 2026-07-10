<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

const ADMIN_EMAIL_DEFAULT = 'admin@nisaticaret.com';
const ADMIN_PHONE_DEFAULT = '+905000000000';

test('creates admin user when none exists', function () {
    $this->seed(AdminUserSeeder::class);

    $admin = User::where('email', ADMIN_EMAIL_DEFAULT)->first();
    expect($admin)->not->toBeNull()
        ->and($admin->phone)->toBe(ADMIN_PHONE_DEFAULT)
        ->and($admin->role)->toBe('admin')
        ->and($admin->hasRole('admin'))->toBeTrue();
});

test('updates existing admin phone when phone is free', function () {
    User::factory()->create([
        'email' => ADMIN_EMAIL_DEFAULT,
        'phone' => '+905999999999',
        'role'  => 'customer',
    ]);

    $this->seed(AdminUserSeeder::class);

    $admin = User::where('email', ADMIN_EMAIL_DEFAULT)->first();
    expect($admin->phone)->toBe(ADMIN_PHONE_DEFAULT)
        ->and($admin->role)->toBe('admin');
});

test('skips phone update when phone belongs to another user', function () {
    $phoneOwner = User::factory()->create([
        'email' => 'other@example.com',
        'phone' => ADMIN_PHONE_DEFAULT,
    ]);
    User::factory()->create([
        'email' => ADMIN_EMAIL_DEFAULT,
        'phone' => '+905999999999',
    ]);

    $this->seed(AdminUserSeeder::class);

    $admin = User::where('email', ADMIN_EMAIL_DEFAULT)->first();
    expect($admin->phone)->toBe('+905999999999')
        ->and($admin->role)->toBe('admin')
        ->and($phoneOwner->fresh()->phone)->toBe(ADMIN_PHONE_DEFAULT);
});

test('does not create admin when phone belongs to another user and admin is missing', function () {
    User::factory()->create([
        'email' => 'other@example.com',
        'phone' => ADMIN_PHONE_DEFAULT,
    ]);

    $this->seed(AdminUserSeeder::class);

    expect(User::where('email', ADMIN_EMAIL_DEFAULT)->exists())->toBeFalse();
});

test('is idempotent across repeated runs', function () {
    $this->seed(AdminUserSeeder::class);
    $this->seed(AdminUserSeeder::class);

    expect(User::where('email', ADMIN_EMAIL_DEFAULT)->count())->toBe(1);
});
