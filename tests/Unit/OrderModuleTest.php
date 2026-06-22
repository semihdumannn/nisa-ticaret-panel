<?php

use App\Models\AppConfig;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Modules\Order\Application\DTOs\AddCartItemDTO;
use App\Modules\Order\Application\DTOs\CreateOrderDTO;
use App\Modules\Order\Application\UseCases\AddToCartUseCase;
use App\Modules\Order\Application\UseCases\CancelOrderUseCase;
use App\Modules\Order\Application\UseCases\CreateOrderUseCase;
use App\Modules\Order\Application\UseCases\GetOrCreateCartUseCase;
use App\Modules\Order\Application\UseCases\RemoveFromCartUseCase;
use App\Modules\Order\Application\UseCases\UpdateCartItemUseCase;
use App\Modules\Order\Application\UseCases\UpdateOrderStatusUseCase;
use App\Modules\Order\Domain\Exceptions\EmptyCartException;
use App\Modules\Order\Domain\Exceptions\InvalidOrderTransitionException;
use App\Modules\Order\Domain\Exceptions\MinimumOrderAmountException;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Domain\ValueObjects\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── OrderStatus enum ──────────────────────────────────────────────────────────

test('OrderStatus has all six cases', function () {
    expect(OrderStatus::cases())->toHaveCount(6);
});

test('OrderStatus labels are defined', function () {
    expect(OrderStatus::PENDING->label())->toBe('Pending');
    expect(OrderStatus::DELIVERED->label())->toBe('Delivered');
    expect(OrderStatus::CANCELLED->label())->toBe('Cancelled');
});

test('OrderStatus valid transitions from pending', function () {
    $allowed = OrderStatus::PENDING->allowedTransitions();
    expect($allowed)->toContain(OrderStatus::CONFIRMED)
        ->and($allowed)->toContain(OrderStatus::CANCELLED);
});

test('OrderStatus canTransitionTo returns correct result', function () {
    expect(OrderStatus::PENDING->canTransitionTo(OrderStatus::CONFIRMED))->toBeTrue();
    expect(OrderStatus::PENDING->canTransitionTo(OrderStatus::PREPARING))->toBeTrue();
    expect(OrderStatus::PENDING->canTransitionTo(OrderStatus::DELIVERED))->toBeTrue();
    expect(OrderStatus::DELIVERED->canTransitionTo(OrderStatus::CANCELLED))->toBeTrue();
    expect(OrderStatus::PENDING->canTransitionTo(OrderStatus::PENDING))->toBeFalse();
});

test('OrderStatus terminal states have no transitions', function () {
    expect(OrderStatus::DELIVERED->isTerminal())->toBeTrue();
    expect(OrderStatus::CANCELLED->isTerminal())->toBeTrue();
    expect(OrderStatus::PENDING->isTerminal())->toBeFalse();
});

test('OrderStatus options returns all statuses', function () {
    expect(OrderStatus::options())->toHaveCount(6)
        ->and(OrderStatus::options()['pending'])->toBe('Pending');
});

// ── PaymentStatus enum ────────────────────────────────────────────────────────

test('PaymentStatus has three cases', function () {
    expect(PaymentStatus::cases())->toHaveCount(3);
});

test('PaymentStatus colors defined', function () {
    expect(PaymentStatus::PAID->color())->toBe('success');
    expect(PaymentStatus::FAILED->color())->toBe('danger');
});

// ── Cart model ────────────────────────────────────────────────────────────────

test('Cart isEmpty returns true when no items', function () {
    $cart = Cart::factory()->create();
    expect($cart->isEmpty())->toBeTrue();
});

test('Cart totalItems sums quantities', function () {
    $user    = User::factory()->create(['phone' => '+905550004001']);
    $cart    = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create();

    CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 3]);
    CartItem::create(['cart_id' => $cart->id, 'product_id' => Product::factory()->create()->id, 'quantity' => 5]);

    expect($cart->totalItems())->toBe(8);
});

// ── GetOrCreateCartUseCase ────────────────────────────────────────────────────

test('GetOrCreateCartUseCase creates a cart on first call', function () {
    $user    = User::factory()->create(['phone' => '+905550004002']);
    $useCase = app(GetOrCreateCartUseCase::class);

    $cart = $useCase->execute($user->id);
    expect($cart->user_id)->toBe($user->id);
    $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
});

test('GetOrCreateCartUseCase returns same cart on second call', function () {
    $user    = User::factory()->create(['phone' => '+905550004003']);
    $useCase = app(GetOrCreateCartUseCase::class);

    $first  = $useCase->execute($user->id);
    $second = $useCase->execute($user->id);
    expect($first->id)->toBe($second->id);
});

// ── AddToCartUseCase ──────────────────────────────────────────────────────────

test('AddToCartUseCase adds a new product to cart', function () {
    $user    = User::factory()->create(['phone' => '+905550004004']);
    $product = Product::factory()->create();
    $cart    = Cart::factory()->create(['user_id' => $user->id]);
    $useCase = app(AddToCartUseCase::class);

    $item = $useCase->execute($cart, new AddCartItemDTO($product->id, 2));
    expect($item->quantity)->toBe(2)
        ->and($item->product_id)->toBe($product->id);
});

test('AddToCartUseCase increments quantity for duplicate product', function () {
    $user    = User::factory()->create(['phone' => '+905550004005']);
    $product = Product::factory()->create();
    $cart    = Cart::factory()->create(['user_id' => $user->id]);
    $useCase = app(AddToCartUseCase::class);

    $useCase->execute($cart, new AddCartItemDTO($product->id, 3));
    $useCase->execute($cart, new AddCartItemDTO($product->id, 2));

    $item = CartItem::where('cart_id', $cart->id)->where('product_id', $product->id)->first();
    expect($item->quantity)->toBe(5);
});

// ── UpdateCartItemUseCase ─────────────────────────────────────────────────────

test('UpdateCartItemUseCase sets new quantity', function () {
    $cart    = Cart::factory()->create();
    $product = Product::factory()->create();
    $item    = CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 5]);
    $useCase = app(UpdateCartItemUseCase::class);

    $updated = $useCase->execute($item, 10);
    expect($updated->quantity)->toBe(10);
});

test('UpdateCartItemUseCase removes item when quantity is zero', function () {
    $cart    = Cart::factory()->create();
    $product = Product::factory()->create();
    $item    = CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 3]);
    $useCase = app(UpdateCartItemUseCase::class);

    $useCase->execute($item, 0);
    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

// ── RemoveFromCartUseCase ─────────────────────────────────────────────────────

test('RemoveFromCartUseCase deletes the cart item', function () {
    $cart    = Cart::factory()->create();
    $product = Product::factory()->create();
    $item    = CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 2]);
    $useCase = app(RemoveFromCartUseCase::class);

    $useCase->execute($item);
    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

// ── CreateOrderUseCase ────────────────────────────────────────────────────────

test('CreateOrderUseCase creates order from cart and reserves stock', function () {
    $user    = User::factory()->create(['phone' => '+905550004006']);
    $product = Product::factory()->create(['price' => 50, 'tax_rate' => 18]);
    $wh      = Warehouse::factory()->create();
    $address = \App\Models\Address::create([
        'user_id' => $user->id, 'full_address' => 'Test Street', 'city' => 'Istanbul',
    ]);

    // Seed stock
    Inventory::create(['product_id' => $product->id, 'warehouse_id' => $wh->id, 'quantity' => 20, 'reserved_quantity' => 0]);

    // Build cart
    $getCart = app(GetOrCreateCartUseCase::class);
    $addItem = app(AddToCartUseCase::class);
    $cart    = $getCart->execute($user->id);
    $addItem->execute($cart, new AddCartItemDTO($product->id, 5));

    // Create order
    $createOrder = app(CreateOrderUseCase::class);
    $order       = $createOrder->execute(new CreateOrderDTO(
        userId:    $user->id,
        addressId: $address->id,
    ));

    expect($order->status)->toBe('pending')
        ->and($order->customer_id)->toBe($user->id)
        ->and($order->items->count())->toBe(1);

    // Stock should be reserved
    $inv = Inventory::where('product_id', $product->id)->first();
    expect($inv->reserved_quantity)->toBe(5);

    // Cart should be empty
    expect(CartItem::where('cart_id', $cart->id)->count())->toBe(0);

    // Order number format
    expect($order->order_number)->toMatch('/^ORD-\d{8}-\d{5}$/');
});

test('CreateOrderUseCase throws EmptyCartException on empty cart', function () {
    $user    = User::factory()->create(['phone' => '+905550004007']);
    $address = \App\Models\Address::create(['user_id' => $user->id, 'full_address' => 'x', 'city' => 'Y']);

    $useCase = app(CreateOrderUseCase::class);

    expect(fn () => $useCase->execute(new CreateOrderDTO(
        userId: $user->id, addressId: $address->id,
    )))->toThrow(EmptyCartException::class);
});

test('CreateOrderUseCase throws InsufficientStockException when not enough stock', function () {
    $user    = User::factory()->create(['phone' => '+905550004008']);
    $product = Product::factory()->create(['price' => 30, 'tax_rate' => 0]);
    $address = \App\Models\Address::create(['user_id' => $user->id, 'full_address' => 'x', 'city' => 'Y']);

    // No inventory

    $getCart = app(GetOrCreateCartUseCase::class);
    $addItem = app(AddToCartUseCase::class);
    $cart    = $getCart->execute($user->id);
    $addItem->execute($cart, new AddCartItemDTO($product->id, 10));

    expect(fn () => app(CreateOrderUseCase::class)->execute(new CreateOrderDTO(
        userId: $user->id, addressId: $address->id,
    )))->toThrow(InsufficientStockException::class);
});

// ── UpdateOrderStatusUseCase ──────────────────────────────────────────────────

test('UpdateOrderStatusUseCase transitions status and records history', function () {
    $order   = Order::factory()->create();
    $useCase = app(UpdateOrderStatusUseCase::class);

    $updated = $useCase->execute($order, OrderStatus::CONFIRMED, 'Admin confirmed.');
    expect($updated->status)->toBe('confirmed');

    $this->assertDatabaseHas('order_status_history', [
        'order_id' => $order->id,
        'status'   => 'confirmed',
    ]);
});

test('UpdateOrderStatusUseCase allows any status transition', function () {
    $order   = Order::factory()->create(); // pending
    $useCase = app(UpdateOrderStatusUseCase::class);

    $updated = $useCase->execute($order, OrderStatus::DELIVERED);
    expect($updated->status)->toBe('delivered');
});

test('UpdateOrderStatusUseCase sets delivered_at when status is delivered', function () {
    $order   = Order::factory()->create(['status' => 'on_the_way']);
    $useCase = app(UpdateOrderStatusUseCase::class);

    $updated = $useCase->execute($order, OrderStatus::DELIVERED);
    expect($updated->delivered_at)->not->toBeNull();
});

// ── CancelOrderUseCase ────────────────────────────────────────────────────────

test('CancelOrderUseCase cancels order and releases reserved stock', function () {
    $user    = User::factory()->create(['phone' => '+905550004009']);
    $product = Product::factory()->create(['price' => 20, 'tax_rate' => 0]);
    $wh      = Warehouse::factory()->create();
    $address = \App\Models\Address::create(['user_id' => $user->id, 'full_address' => 'x', 'city' => 'Y']);

    Inventory::create(['product_id' => $product->id, 'warehouse_id' => $wh->id, 'quantity' => 20, 'reserved_quantity' => 0]);

    // Place order (reserves stock)
    $getCart = app(GetOrCreateCartUseCase::class);
    $addItem = app(AddToCartUseCase::class);
    $addItem->execute($getCart->execute($user->id), new AddCartItemDTO($product->id, 8));
    $order = app(CreateOrderUseCase::class)->execute(new CreateOrderDTO($user->id, $address->id));

    // Verify reservation
    expect(Inventory::where('product_id', $product->id)->first()->reserved_quantity)->toBe(8);

    // Cancel
    $cancelled = app(CancelOrderUseCase::class)->execute($order, 'Customer request');
    expect($cancelled->status)->toBe('cancelled');

    // Reservation released
    expect(Inventory::where('product_id', $product->id)->first()->reserved_quantity)->toBe(0);
});

test('CancelOrderUseCase can cancel a delivered order', function () {
    $order     = Order::factory()->delivered()->create();
    $cancelled = app(CancelOrderUseCase::class)->execute($order);
    expect($cancelled->status)->toBe('cancelled');
});

// ── MinimumOrderAmountException ───────────────────────────────────────────────

it('throws MinimumOrderAmountException when subtotal is below configured minimum', function () {
    // Seed a low minimum so we can trigger the exception
    AppConfig::where('key', 'min_order_amount')->updateOrInsert(
        ['key' => 'min_order_amount'],
        ['value' => '500', 'type' => 'number', 'description' => 'test']
    );
    AppConfig::flushCache();

    $exception = new MinimumOrderAmountException(minimum: 500.0, actual: 150.0);

    expect($exception)->toBeInstanceOf(\RuntimeException::class)
        ->and($exception->getMinimum())->toBe(500.0)
        ->and($exception->getActual())->toBe(150.0)
        ->and($exception->getCode())->toBe(422)
        ->and($exception->getMessage())->toContain('500');
});
