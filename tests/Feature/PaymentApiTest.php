<?php

use App\Models\Order;
use App\Models\User;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Domain\ValueObjects\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Helpers ───────────────────────────────────────────────────────────────────

function paymentCustomer(): User
{
    return User::factory()->create(['role' => 'customer', 'is_active' => true]);
}

function paymentPendingOrder(User $customer): Order
{
    return Order::factory()->create([
        'customer_id'    => $customer->id,
        'status'         => OrderStatus::PENDING->value,
        'payment_status' => PaymentStatus::PENDING->value,
        'total'          => 250.00,
        'payment_token'  => null, // explicit
    ]);
}

// ── initiate ──────────────────────────────────────────────────────────────────

test('customer can initiate iyzico payment for their own pending order', function () {
    $customer = paymentCustomer();
    $order    = paymentPendingOrder($customer);

    $this->mock(\App\Modules\Order\Domain\Contracts\PaymentServiceInterface::class)
        ->shouldReceive('initializeCheckout')
        ->once()
        ->andReturn([
            'success'           => true,
            'checkout_form_url' => 'https://sandbox-api.iyzipay.com/checkout/form/abc123',
            'token'             => 'abc123',
        ]);

    $this->actingAs($customer, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/pay")
        ->assertOk()
        ->assertJsonStructure(['checkout_url', 'token']);
});

test('customer cannot pay for another customers order', function () {
    $customer = paymentCustomer();
    $other    = paymentCustomer();
    $order    = paymentPendingOrder($other);

    $this->actingAs($customer, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/pay")
        ->assertForbidden();
});

test('cannot initiate payment for already paid order', function () {
    $customer = paymentCustomer();
    $order    = Order::factory()->create([
        'customer_id'    => $customer->id,
        'status'         => OrderStatus::CONFIRMED->value,
        'payment_status' => PaymentStatus::PAID->value,
    ]);

    $this->actingAs($customer, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/pay")
        ->assertUnprocessable()
        ->assertJsonFragment(['message' => 'Order is already paid.']);
});

test('cannot initiate payment for cancelled order', function () {
    $customer = paymentCustomer();
    $order    = Order::factory()->create([
        'customer_id'    => $customer->id,
        'status'         => OrderStatus::CANCELLED->value,
        'payment_status' => PaymentStatus::PENDING->value,
    ]);

    $this->actingAs($customer, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/pay")
        ->assertUnprocessable()
        ->assertJsonFragment(['message' => 'Payment is not available for this order.']);
});

test('returns 503 when iyzico service fails', function () {
    $customer = paymentCustomer();
    $order    = paymentPendingOrder($customer);

    $this->mock(\App\Modules\Order\Domain\Contracts\PaymentServiceInterface::class)
        ->shouldReceive('initializeCheckout')
        ->once()
        ->andReturn([
            'success'  => false,
            'message'  => 'iyzico connection error.',
        ]);

    $this->actingAs($customer, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/pay")
        ->assertStatus(503);
});

// ── callback ─────────────────────────────────────────────────────────────────

test('callback marks order as paid and confirms it on success', function () {
    $customer = paymentCustomer();
    $order    = paymentPendingOrder($customer);
    $order->update(['payment_token' => 'tok_success']);

    $this->mock(\App\Modules\Order\Domain\Contracts\PaymentServiceInterface::class)
        ->shouldReceive('retrieveCheckoutForm')
        ->once()
        ->with('tok_success')
        ->andReturn([
            'success'         => true,
            'payment_id'      => 'pay_12345',
            'conversation_id' => (string) $order->id,
            'fraud_status'    => 1,
            'error_code'      => null,
            'error_message'   => null,
        ]);

    $this->postJson('/api/v1/payment/callback', ['token' => 'tok_success'])
        ->assertOk()
        ->assertJsonFragment(['message' => 'Payment successful.', 'payment_id' => 'pay_12345']);

    $this->assertDatabaseHas('orders', [
        'id'                => $order->id,
        'payment_status'    => PaymentStatus::PAID->value,
        'payment_method'    => 'iyzico',
        'payment_reference' => 'pay_12345',
        'status'            => OrderStatus::CONFIRMED->value,
    ]);
});

test('callback returns 422 when token is missing', function () {
    $this->postJson('/api/v1/payment/callback', [])
        ->assertUnprocessable()
        ->assertJsonFragment(['message' => 'Missing payment token.']);
});

test('callback marks order as failed when iyzico returns failure', function () {
    $customer = paymentCustomer();
    $order    = paymentPendingOrder($customer);
    $order->update(['payment_token' => 'tok_fail']);

    $this->mock(\App\Modules\Order\Domain\Contracts\PaymentServiceInterface::class)
        ->shouldReceive('retrieveCheckoutForm')
        ->once()
        ->andReturn([
            'success'         => false,
            'payment_id'      => null,
            'conversation_id' => (string) $order->id,
            'error_code'      => '10051',
            'error_message'   => 'Insufficient funds.',
        ]);

    $this->postJson('/api/v1/payment/callback', ['token' => 'tok_fail'])
        ->assertStatus(402)
        ->assertJsonFragment(['error_code' => '10051']);

    $this->assertDatabaseHas('orders', [
        'id'             => $order->id,
        'payment_status' => PaymentStatus::FAILED->value,
    ]);
});

test('callback is idempotent for already paid order', function () {
    $customer = paymentCustomer();
    $order    = Order::factory()->create([
        'customer_id'       => $customer->id,
        'status'            => OrderStatus::CONFIRMED->value,
        'payment_status'    => PaymentStatus::PAID->value,
        'payment_reference' => 'pay_existing',
        'payment_token'     => 'tok_dup',
    ]);

    $this->mock(\App\Modules\Order\Domain\Contracts\PaymentServiceInterface::class)
        ->shouldReceive('retrieveCheckoutForm')
        ->once()
        ->andReturn([
            'success'         => true,
            'payment_id'      => 'pay_duplicate',
            'conversation_id' => (string) $order->id,
        ]);

    $this->postJson('/api/v1/payment/callback', ['token' => 'tok_dup'])
        ->assertOk()
        ->assertJsonFragment(['message' => 'Payment already recorded.']);

    // Status should NOT have changed
    $this->assertDatabaseHas('orders', [
        'id'             => $order->id,
        'payment_status' => PaymentStatus::PAID->value,
    ]);
});

test('callback rejects token not associated with any order', function () {
    // No order has this token stored
    $this->mock(\App\Modules\Order\Domain\Contracts\PaymentServiceInterface::class)
        ->shouldNotReceive('retrieveCheckoutForm'); // iyzico should NOT be called

    $this->postJson('/api/v1/payment/callback', ['token' => 'tok_unknown_xyz'])
        ->assertNotFound()
        ->assertJsonFragment(['message' => 'Order not found.']);
});

test('initiate stores payment token on order', function () {
    $customer = paymentCustomer();
    $order    = paymentPendingOrder($customer);

    $this->mock(\App\Modules\Order\Domain\Contracts\PaymentServiceInterface::class)
        ->shouldReceive('initializeCheckout')
        ->once()
        ->andReturn([
            'success'           => true,
            'checkout_form_url' => 'https://sandbox-api.iyzipay.com/checkout/form/abc123',
            'token'             => 'tok_stored_abc123',
        ]);

    $this->actingAs($customer, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/pay")
        ->assertOk();

    $this->assertDatabaseHas('orders', [
        'id'            => $order->id,
        'payment_token' => 'tok_stored_abc123',
    ]);
});
