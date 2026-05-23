<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAuthenticatedUser(array $attributes = []): array
{
    $user  = User::factory()->create(array_merge(['phone' => '+905550000099'], $attributes));
    $user->profile()->create([]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

// ── GET /api/v1/profile ───────────────────────────────────────────────────────

test('authenticated user can view their profile', function () {
    [$user, $token] = createAuthenticatedUser();

    $this->withToken($token)
        ->getJson('/api/v1/profile')
        ->assertStatus(200)
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonStructure(['user' => ['id', 'name', 'phone', 'role', 'profile']]);
});

test('unauthenticated user cannot view profile', function () {
    $this->getJson('/api/v1/profile')
        ->assertStatus(401);
});

// ── PUT /api/v1/profile ───────────────────────────────────────────────────────

test('user can update their name', function () {
    [$user, $token] = createAuthenticatedUser(['name' => 'Old Name']);

    $this->withToken($token)
        ->putJson('/api/v1/profile', ['name' => 'New Name'])
        ->assertStatus(200)
        ->assertJsonPath('user.name', 'New Name');

    expect($user->fresh()->name)->toBe('New Name');
});

test('user can update company name in profile', function () {
    [$user, $token] = createAuthenticatedUser();

    $this->withToken($token)
        ->putJson('/api/v1/profile', ['company_name' => 'ACME Inc'])
        ->assertStatus(200);

    expect($user->profile->fresh()->company_name)->toBe('ACME Inc');
});

test('user cannot use duplicate email', function () {
    [$user1, $token] = createAuthenticatedUser(['phone' => '+905550000100', 'email' => 'first@example.com']);
    User::factory()->create(['phone' => '+905550000101', 'email' => 'taken@example.com']);

    $this->withToken($token)
        ->putJson('/api/v1/profile', ['email' => 'taken@example.com'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('partial updates do not affect other fields', function () {
    [$user, $token] = createAuthenticatedUser(['name' => 'Keep Me', 'phone' => '+905550000200']);

    $this->withToken($token)
        ->putJson('/api/v1/profile', ['company_name' => 'Updated Corp'])
        ->assertStatus(200);

    expect($user->fresh()->name)->toBe('Keep Me');
});
