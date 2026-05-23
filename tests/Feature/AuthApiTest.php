<?php

use App\Models\User;
use App\Modules\User\Domain\Contracts\FirebaseAuthInterface;
use App\Modules\User\Domain\Exceptions\InvalidFirebaseTokenException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Firebase Login ────────────────────────────────────────────────────────────

test('firebase login creates a new user and returns token', function () {
    // Mock Firebase service
    $this->mock(FirebaseAuthInterface::class)
        ->shouldReceive('verifyIdToken')
        ->once()
        ->with('valid-firebase-token')
        ->andReturn([
            'uid'          => 'firebase-uid-abc',
            'phone_number' => '+905551234567',
            'email'        => 'new@example.com',
            'name'         => 'New User',
        ]);

    $response = $this->postJson('/api/v1/auth/firebase-login', [
        'id_token'    => 'valid-firebase-token',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'token',
            'token_type',
            'user' => ['id', 'name', 'email', 'phone', 'role'],
            'is_new_user',
        ]);

    expect($response->json('is_new_user'))->toBeTrue();
    expect($response->json('token_type'))->toBe('Bearer');

    $this->assertDatabaseHas('users', [
        'firebase_uid' => 'firebase-uid-abc',
        'phone'        => '+905551234567',
    ]);
});

test('firebase login returns existing user on second login', function () {
    $existingUser = User::factory()->create([
        'firebase_uid' => 'existing-uid',
        'phone'        => '+905557654321',
        'email'        => 'existing@example.com',
    ]);
    $existingUser->profile()->create([]);

    $this->mock(FirebaseAuthInterface::class)
        ->shouldReceive('verifyIdToken')
        ->once()
        ->andReturn([
            'uid'          => 'existing-uid',
            'phone_number' => '+905557654321',
            'email'        => 'existing@example.com',
            'name'         => 'Existing User',
        ]);

    $response = $this->postJson('/api/v1/auth/firebase-login', [
        'id_token' => 'valid-token',
    ]);

    $response->assertStatus(200);
    expect($response->json('is_new_user'))->toBeFalse();
    expect($response->json('user.id'))->toBe($existingUser->id);
});

test('firebase login returns 401 for invalid token', function () {
    $this->mock(FirebaseAuthInterface::class)
        ->shouldReceive('verifyIdToken')
        ->once()
        ->andThrow(new InvalidFirebaseTokenException('Token expired'));

    $response = $this->postJson('/api/v1/auth/firebase-login', [
        'id_token' => 'invalid-token',
    ]);

    $response->assertStatus(401)
        ->assertJson(['error' => 'INVALID_TOKEN']);
});

test('firebase login requires id_token', function () {
    $response = $this->postJson('/api/v1/auth/firebase-login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['id_token']);
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
