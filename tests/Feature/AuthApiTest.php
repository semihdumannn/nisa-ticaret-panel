<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;

uses(RefreshDatabase::class);

// ── Device Register ─────────────────────────────────────────────────────────

test('device register creates a new user and returns token and totp secret', function () {
    $response = $this->postJson('/api/v1/auth/device-register', [
        'phone'       => '+905551234567',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'token',
            'token_type',
            'user' => ['id', 'name', 'email', 'phone', 'role'],
            'totp_secret',
            'totp_period',
            'is_new_user',
        ]);

    expect($response->json('is_new_user'))->toBeTrue();
    expect($response->json('token_type'))->toBe('Bearer');
    expect($response->json('totp_secret'))->not->toBeEmpty();

    $this->assertDatabaseHas('users', [
        'phone' => '+905551234567',
    ]);
});

test('device register returns existing user with existing totp secret', function () {
    $existingUser = User::factory()->create([
        'phone'       => '+905557654321',
        'email'       => 'existing@example.com',
        'totp_secret' => 'EXISTINGSECRETKEY234567',
    ]);
    $existingUser->profile()->create([]);

    $response = $this->postJson('/api/v1/auth/device-register', [
        'phone' => '+905557654321',
    ]);

    $response->assertStatus(200);
    expect($response->json('is_new_user'))->toBeFalse();
    expect($response->json('user.id'))->toBe($existingUser->id);
    expect($response->json('totp_secret'))->toBe('EXISTINGSECRETKEY234567');
});

test('device register requires phone', function () {
    $response = $this->postJson('/api/v1/auth/device-register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

// ── TOTP Login ────────────────────────────────────────────────────────────────

test('totp login returns token for valid code', function () {
    $google2fa = app(Google2FA::class);
    $secret    = $google2fa->generateSecretKey();

    $user = User::factory()->create([
        'phone'       => '+905550009999',
        'totp_secret' => $secret,
    ]);
    $user->profile()->create([]);

    $code = $google2fa->getCurrentOtp($secret);

    $response = $this->postJson('/api/v1/auth/totp-login', [
        'phone' => '+905550009999',
        'code'  => $code,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'token',
            'token_type',
            'user' => ['id', 'name', 'email', 'phone', 'role'],
        ]);

    expect($response->json('user.id'))->toBe($user->id);
});

test('totp login returns 401 for invalid code', function () {
    $google2fa = app(Google2FA::class);
    $secret    = $google2fa->generateSecretKey();

    User::factory()->create([
        'phone'       => '+905550008888',
        'totp_secret' => $secret,
    ])->profile()->create([]);

    $response = $this->postJson('/api/v1/auth/totp-login', [
        'phone' => '+905550008888',
        'code'  => '000000',
    ]);

    $response->assertStatus(401)
        ->assertJson(['error' => 'INVALID_TOTP']);
});

test('totp login returns 401 for unknown phone', function () {
    $response = $this->postJson('/api/v1/auth/totp-login', [
        'phone' => '+905550007777',
        'code'  => '123456',
    ]);

    $response->assertStatus(401)
        ->assertJson(['error' => 'INVALID_TOTP']);
});

// ── Server Time ───────────────────────────────────────────────────────────────

test('server time returns current timestamp', function () {
    $response = $this->getJson('/api/v1/auth/server-time');

    $response->assertStatus(200)
        ->assertJsonStructure(['timestamp']);

    expect($response->json('timestamp'))->toBeInt();
});

// ── Logout ────────────────────────────────────────────────────────────────────

test('authenticated user can logout and token is deleted', function () {
    $user  = User::factory()->create(['phone' => '+905550000001']);
    $token = $user->createToken('test')->plainTextToken;

    // Grab the token ID before deletion
    $tokenId = $user->tokens()->first()->id;

    $this->withToken($token)
        ->postJson('/api/v1/auth/logout')
        ->assertStatus(200)
        ->assertJson(['message' => 'Logged out successfully.']);

    // Token record must be removed from DB
    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
});

test('unauthenticated user cannot logout', function () {
    $this->postJson('/api/v1/auth/logout')
        ->assertStatus(401);
});

// ── Me ────────────────────────────────────────────────────────────────────────

test('authenticated user can get their profile via /me', function () {
    $user = User::factory()->create(['phone' => '+905550000002']);
    $user->profile()->create([]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.phone', $user->phone);
});

test('unauthenticated user cannot access /me', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertStatus(401);
});
