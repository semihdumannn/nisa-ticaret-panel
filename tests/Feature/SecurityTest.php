<?php

/**
 * Phase 8 — Security & Infrastructure Tests
 *
 * Covers:
 *  - Security headers on API responses
 *  - Global exception handler (404, 405, 422, 401, 403, 500)
 *  - Rate limit throttle responses
 *  - Health check endpoint structure
 */

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ── Security Headers ──────────────────────────────────────────────────────────

test('API responses include X-Content-Type-Options header', function () {
    $this->getJson('/api/v1/health')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('API responses include X-Frame-Options header', function () {
    $this->getJson('/api/v1/health')
        ->assertHeader('X-Frame-Options', 'DENY');
});

test('API responses include X-XSS-Protection header', function () {
    $this->getJson('/api/v1/health')
        ->assertHeader('X-XSS-Protection', '1; mode=block');
});

test('API responses include Referrer-Policy header', function () {
    $this->getJson('/api/v1/health')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('API responses include Permissions-Policy header', function () {
    $response = $this->getJson('/api/v1/health');
    expect($response->headers->has('Permissions-Policy'))->toBeTrue();
});

// ── Exception Handler: 404 ────────────────────────────────────────────────────

test('unknown API route returns JSON 404', function () {
    $this->getJson('/api/v1/this-route-does-not-exist')
        ->assertStatus(404)
        ->assertJsonStructure(['message']);
});

test('model-not-found returns JSON 404 with message', function () {
    $this->getJson('/api/v1/products/999999')
        ->assertStatus(404)
        ->assertJsonStructure(['message']);
});

// ── Exception Handler: 405 ────────────────────────────────────────────────────

test('wrong HTTP method returns JSON 405', function () {
    // DELETE is not defined for /api/v1/health
    $this->deleteJson('/api/v1/health')
        ->assertStatus(405)
        ->assertJsonStructure(['message']);
});

// ── Exception Handler: 401 ────────────────────────────────────────────────────

test('unauthenticated request returns JSON 401', function () {
    $this->getJson('/api/v1/orders')
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

// ── Exception Handler: 403 ────────────────────────────────────────────────────

test('unauthorized role returns JSON 403', function () {
    $user  = User::factory()->create(['phone' => '+905558001001']);
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/admin/analytics/dashboard')
        ->assertStatus(403);
});

// ── Exception Handler: 422 ────────────────────────────────────────────────────

test('validation failure returns JSON 422 with errors key', function () {
    $this->postJson('/api/v1/auth/firebase-login', [])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors']);
});

test('validation error response has correct message', function () {
    $this->postJson('/api/v1/auth/firebase-login', [])
        ->assertStatus(422)
        ->assertJsonPath('message', 'The given data was invalid.');
});

// ── Rate Limiting ─────────────────────────────────────────────────────────────

test('login endpoint is throttled after 10 requests', function () {
    // Clear the rate limiter key matching our AppServiceProvider format
    RateLimiter::clear('login:127.0.0.1');
    // Also clear any format variations
    RateLimiter::resetAttempts('login:127.0.0.1');

    // Burn through the 10-request limit
    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/api/v1/auth/firebase-login', ['firebase_token' => 'bad']);
    }

    // The 11th should be throttled
    $this->postJson('/api/v1/auth/firebase-login', ['firebase_token' => 'bad'])
        ->assertStatus(429)
        ->assertJsonStructure(['message', 'retry_after']);
});

test('throttled response includes retry_after field', function () {
    RateLimiter::clear('login:127.0.0.1');

    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/api/v1/auth/firebase-login', ['firebase_token' => 'bad']);
    }

    $response = $this->postJson('/api/v1/auth/firebase-login', ['firebase_token' => 'bad'])
        ->assertStatus(429);

    expect($response->json('retry_after'))->toBeInt()->toBeGreaterThan(0);
});

// ── Health Check ──────────────────────────────────────────────────────────────

test('health check returns 200 with expected structure', function () {
    $this->getJson('/api/v1/health')
        ->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'app',
            'version',
            'environment',
            'timestamp',
            'checks' => [
                'database' => ['status', 'driver'],
                'cache'    => ['status', 'driver'],
                'queue'    => ['status', 'driver'],
                'storage'  => ['status', 'disk'],
            ],
        ]);
});

test('health check database status is ok', function () {
    $response = $this->getJson('/api/v1/health')->assertStatus(200);
    expect($response->json('checks.database.status'))->toBe('ok');
});

test('health check cache status is ok', function () {
    $response = $this->getJson('/api/v1/health')->assertStatus(200);
    expect($response->json('checks.cache.status'))->toBe('ok');
});

test('health check queue status is ok', function () {
    $response = $this->getJson('/api/v1/health')->assertStatus(200);
    expect($response->json('checks.queue.status'))->toBe('ok');
});

test('health check storage status is ok', function () {
    $response = $this->getJson('/api/v1/health')->assertStatus(200);
    expect($response->json('checks.storage.status'))->toBe('ok');
});

test('health check overall status is ok when all checks pass', function () {
    $response = $this->getJson('/api/v1/health')->assertStatus(200);
    expect($response->json('status'))->toBe('ok');
});
