<?php

use App\Models\FaqItem;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('anyone can list active faq items grouped by category', function () {
    FaqItem::create(['category' => 'Sipariş', 'question' => 'Q1', 'answer' => 'A1', 'sort_order' => 1, 'is_active' => true]);
    FaqItem::create(['category' => 'Sipariş', 'question' => 'Q2', 'answer' => 'A2', 'sort_order' => 2, 'is_active' => true]);
    FaqItem::create(['category' => 'Teslimat', 'question' => 'Q3', 'answer' => 'A3', 'sort_order' => 1, 'is_active' => true]);
    FaqItem::create(['category' => 'Sipariş', 'question' => 'Q4', 'answer' => 'A4', 'sort_order' => 3, 'is_active' => false]);

    $this->getJson('/api/v1/help/faq')
        ->assertOk()
        ->assertJsonStructure(['categories' => [['name', 'items' => [['id', 'question', 'answer']]]]])
        ->assertJsonCount(2, 'categories');
});

test('inactive faq items are excluded from public list', function () {
    FaqItem::create(['category' => 'Ödeme', 'question' => 'Q1', 'answer' => 'A1', 'is_active' => false]);

    $response = $this->getJson('/api/v1/help/faq')->assertOk();
    $this->assertEmpty($response->json('categories'));
});

test('admin can create faq item', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $admin->assignRole('admin');

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/admin/faq', [
            'category' => 'Hesap',
            'question' => 'Şifremi unuttum?',
            'answer'   => 'Şifremi unuttum butonuna tıklayın.',
            'sort_order'    => 1,
        ])
        ->assertCreated()
        ->assertJsonPath('category', 'Hesap');
});

test('admin can delete faq item', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $admin->assignRole('admin');
    $item  = FaqItem::create(['category' => 'Test', 'question' => 'Q', 'answer' => 'A', 'is_active' => true]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/admin/faq/{$item->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('faq_items', ['id' => $item->id]);
});

test('non-admin cannot access admin faq endpoints', function () {
    $user = User::factory()->create(['role' => 'customer', 'is_active' => true]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/admin/faq', ['category' => 'X', 'question' => 'Q', 'answer' => 'A'])
        ->assertForbidden();
});
