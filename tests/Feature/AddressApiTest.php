<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeUser(string $phone = '+905550000001'): array
{
    $user  = User::factory()->create(['phone' => $phone]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

// ── GET /api/v1/addresses ─────────────────────────────────────────────────────

test('user can list their addresses', function () {
    [$user, $token] = makeUser();

    $user->addresses()->createMany([
        ['full_address' => 'Address 1', 'is_default' => true],
        ['full_address' => 'Address 2', 'is_default' => false],
    ]);

    $this->withToken($token)
        ->getJson('/api/v1/addresses')
        ->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('user sees empty list when no addresses', function () {
    [$user, $token] = makeUser('+905550000002');

    $this->withToken($token)
        ->getJson('/api/v1/addresses')
        ->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

// ── POST /api/v1/addresses ────────────────────────────────────────────────────

test('user can create an address', function () {
    [$user, $token] = makeUser('+905550000003');

    $response = $this->withToken($token)
        ->postJson('/api/v1/addresses', [
            'full_address' => 'Atatürk Cad No:5, Kadıköy',
            'city'         => 'Istanbul',
            'district'     => 'Kadıköy',
            'is_default'   => true,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('address.city', 'Istanbul')
        ->assertJsonPath('address.is_default', true);

    $this->assertDatabaseHas('addresses', [
        'user_id'      => $user->id,
        'full_address' => 'Atatürk Cad No:5, Kadıköy',
    ]);
});

test('address creation requires full_address', function () {
    [$user, $token] = makeUser('+905550000004');

    $this->withToken($token)
        ->postJson('/api/v1/addresses', ['city' => 'Istanbul'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['full_address']);
});

test('creating a default address unsets previous default', function () {
    [$user, $token] = makeUser('+905550000005');

    $first = $user->addresses()->create(['full_address' => 'Old Default', 'is_default' => true]);

    $this->withToken($token)
        ->postJson('/api/v1/addresses', [
            'full_address' => 'New Default',
            'is_default'   => true,
        ])
        ->assertStatus(201);

    expect($first->fresh()->is_default)->toBeFalse();
});

// ── PUT /api/v1/addresses/{address} ──────────────────────────────────────────

test('user can update their address', function () {
    [$user, $token] = makeUser('+905550000006');
    $address = $user->addresses()->create(['full_address' => 'Old St', 'is_default' => false]);

    $this->withToken($token)
        ->putJson("/api/v1/addresses/{$address->id}", [
            'full_address' => 'New Street 42',
        ])
        ->assertStatus(200)
        ->assertJsonPath('address.full_address', 'New Street 42');
});

test('user cannot update address belonging to another user', function () {
    [$user1, $token] = makeUser('+905550000007');
    $otherUser       = User::factory()->create(['phone' => '+905550000008']);
    $address         = $otherUser->addresses()->create(['full_address' => 'Private St']);

    $this->withToken($token)
        ->putJson("/api/v1/addresses/{$address->id}", [
            'full_address' => 'Hacked!',
        ])
        ->assertStatus(403);
});

// ── DELETE /api/v1/addresses/{address} ───────────────────────────────────────

test('user can delete their address', function () {
    [$user, $token] = makeUser('+905550000009');
    $address = $user->addresses()->create(['full_address' => 'To Delete']);

    $this->withToken($token)
        ->deleteJson("/api/v1/addresses/{$address->id}")
        ->assertStatus(200);

    $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
});

test('user cannot delete address belonging to another user', function () {
    [$user1, $token] = makeUser('+905550000010');
    $otherUser       = User::factory()->create(['phone' => '+905550000011']);
    $address         = $otherUser->addresses()->create(['full_address' => 'Protected']);

    $this->withToken($token)
        ->deleteJson("/api/v1/addresses/{$address->id}")
        ->assertStatus(403);
});

// ── POST /api/v1/addresses/{address}/set-default ──────────────────────────────

test('user can set an address as default', function () {
    [$user, $token] = makeUser('+905550000012');
    $first  = $user->addresses()->create(['full_address' => 'First',  'is_default' => true]);
    $second = $user->addresses()->create(['full_address' => 'Second', 'is_default' => false]);

    $this->withToken($token)
        ->postJson("/api/v1/addresses/{$second->id}/set-default")
        ->assertStatus(200)
        ->assertJsonPath('address.is_default', true);

    expect($first->fresh()->is_default)->toBeFalse();
    expect($second->fresh()->is_default)->toBeTrue();
});
