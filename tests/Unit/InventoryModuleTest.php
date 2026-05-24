<?php

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Modules\Inventory\Application\UseCases\AdjustStockUseCase;
use App\Modules\Inventory\Application\UseCases\CheckLowStockUseCase;
use App\Modules\Inventory\Application\UseCases\DispatchStockUseCase;
use App\Modules\Inventory\Application\UseCases\ReceiveStockUseCase;
use App\Modules\Inventory\Application\UseCases\ReserveStockUseCase;
use App\Modules\Inventory\Application\UseCases\TransferStockUseCase;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Domain\ValueObjects\MovementType;

// ── MovementType enum ────────────────────────────────────────────────────────

test('MovementType has all four cases', function () {
    expect(MovementType::cases())->toHaveCount(4);
});

test('MovementType returns correct labels', function () {
    expect(MovementType::IN->label())->toBe('Stock In');
    expect(MovementType::OUT->label())->toBe('Stock Out');
    expect(MovementType::TRANSFER->label())->toBe('Transfer');
    expect(MovementType::ADJUSTMENT->label())->toBe('Adjustment');
});

test('MovementType returns correct colors', function () {
    expect(MovementType::IN->color())->toBe('success');
    expect(MovementType::OUT->color())->toBe('danger');
    expect(MovementType::TRANSFER->color())->toBe('warning');
    expect(MovementType::ADJUSTMENT->color())->toBe('info');
});

test('MovementType options returns key-value map', function () {
    $options = MovementType::options();
    expect($options)->toHaveCount(4)
        ->and($options['in'])->toBe('Stock In')
        ->and($options['out'])->toBe('Stock Out');
});

// ── InsufficientStockException ───────────────────────────────────────────────

test('InsufficientStockException stores requested and available', function () {
    $e = new InsufficientStockException(requested: 10, available: 3);
    expect($e->requested)->toBe(10)
        ->and($e->available)->toBe(3)
        ->and($e->getCode())->toBe(422)
        ->and($e->getMessage())->toContain('10')
        ->and($e->getMessage())->toContain('3');
});

// ── Inventory model ──────────────────────────────────────────────────────────

test('availableQuantity = quantity minus reserved', function () {
    $inv = new Inventory(['quantity' => 50, 'reserved_quantity' => 12]);
    expect($inv->availableQuantity())->toBe(38);
});

test('availableQuantity never goes below zero', function () {
    $inv = new Inventory(['quantity' => 5, 'reserved_quantity' => 20]);
    expect($inv->availableQuantity())->toBe(0);
});

test('isLowStock true when available <= threshold', function () {
    $inv = new Inventory(['quantity' => 5, 'reserved_quantity' => 1]);
    expect($inv->isLowStock(5))->toBeTrue();  // available = 4
});

test('isLowStock false when above threshold', function () {
    $inv = new Inventory(['quantity' => 20, 'reserved_quantity' => 0]);
    expect($inv->isLowStock(5))->toBeFalse();
});

test('isLowStock uses default threshold of 5', function () {
    $inv = new Inventory(['quantity' => 5, 'reserved_quantity' => 0]);
    expect($inv->isLowStock())->toBeTrue();  // available = 5, exactly at threshold

    $inv2 = new Inventory(['quantity' => 6, 'reserved_quantity' => 0]);
    expect($inv2->isLowStock())->toBeFalse(); // available = 6
});

// ── Warehouse model ──────────────────────────────────────────────────────────

test('Warehouse active scope filters inactive', function () {
    $wh1 = Warehouse::factory()->create(['is_active' => true]);
    $wh2 = Warehouse::factory()->inactive()->create();

    $active = Warehouse::active()->get();
    expect($active->contains($wh1))->toBeTrue()
        ->and($active->contains($wh2))->toBeFalse();
});

test('Warehouse has inventory and stockMovements relations', function () {
    $wh = new Warehouse;
    expect(method_exists($wh, 'inventory'))->toBeTrue()
        ->and(method_exists($wh, 'stockMovements'))->toBeTrue();
});

// ── Inventory scopes ──────────────────────────────────────────────────────────

test('Inventory lowStock scope returns correct records', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    // quantity=3, reserved=0 → available=3 → low stock
    Inventory::create(['product_id' => $product->id, 'warehouse_id' => $wh->id, 'quantity' => 3, 'reserved_quantity' => 0]);
    // quantity=50, reserved=0 → available=50 → not low
    Inventory::create(['product_id' => $product->id, 'warehouse_id' => Warehouse::factory()->create()->id, 'quantity' => 50, 'reserved_quantity' => 0]);

    $low = Inventory::lowStock(5)->get();
    expect($low)->toHaveCount(1)
        ->and($low->first()->quantity)->toBe(3);
});

test('Inventory outOfStock scope returns zero-available records', function () {
    $product = Product::factory()->create();
    $wh1     = Warehouse::factory()->create();
    $wh2     = Warehouse::factory()->create();

    // qty=10, reserved=10 → available=0
    Inventory::create(['product_id' => $product->id, 'warehouse_id' => $wh1->id, 'quantity' => 10, 'reserved_quantity' => 10]);
    // qty=0, reserved=0 → available=0
    Inventory::create(['product_id' => $product->id, 'warehouse_id' => $wh2->id, 'quantity' => 0, 'reserved_quantity' => 0]);
    // qty=5, reserved=0 → available=5 → in stock
    Inventory::create(['product_id' => $product->id, 'warehouse_id' => Warehouse::factory()->create()->id, 'quantity' => 5, 'reserved_quantity' => 0]);

    $oos = Inventory::outOfStock()->get();
    expect($oos)->toHaveCount(2);
});

// ── StockMovement scopes ──────────────────────────────────────────────────────

test('StockMovement ofType scope filters by type', function () {
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    StockMovement::create(['product_id' => $product->id, 'warehouse_id' => $wh->id, 'type' => 'in',  'quantity' => 10, 'reason' => 'test']);
    StockMovement::create(['product_id' => $product->id, 'warehouse_id' => $wh->id, 'type' => 'out', 'quantity' => 5,  'reason' => 'test']);

    expect(StockMovement::ofType('in')->count())->toBe(1)
        ->and(StockMovement::ofType('out')->count())->toBe(1);
});

test('StockMovement forProduct scope filters correctly', function () {
    $p1 = Product::factory()->create();
    $p2 = Product::factory()->create();
    $wh = Warehouse::factory()->create();

    StockMovement::create(['product_id' => $p1->id, 'warehouse_id' => $wh->id, 'type' => 'in', 'quantity' => 1, 'reason' => 'x']);
    StockMovement::create(['product_id' => $p2->id, 'warehouse_id' => $wh->id, 'type' => 'in', 'quantity' => 1, 'reason' => 'x']);

    expect(StockMovement::forProduct($p1->id)->count())->toBe(1);
});

// ── ReceiveStockUseCase ───────────────────────────────────────────────────────

test('ReceiveStockUseCase increments quantity and records movement', function () {
    $useCase = app(ReceiveStockUseCase::class);
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    $dto = new \App\Modules\Inventory\Application\DTOs\StockOperationDTO(
        productId:   $product->id,
        warehouseId: $wh->id,
        quantity:    20,
        reason:      'Initial stock',
    );

    $inventory = $useCase->execute($dto);

    expect($inventory->quantity)->toBe(20)
        ->and($inventory->last_restock_date)->not->toBeNull();

    $this->assertDatabaseHas('stock_movements', [
        'product_id'   => $product->id,
        'warehouse_id' => $wh->id,
        'type'         => 'in',
        'quantity'     => 20,
    ]);
});

test('ReceiveStockUseCase accumulates on second call', function () {
    $useCase = app(ReceiveStockUseCase::class);
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    $dto = new \App\Modules\Inventory\Application\DTOs\StockOperationDTO(
        productId:   $product->id,
        warehouseId: $wh->id,
        quantity:    10,
        reason:      'First batch',
    );

    $useCase->execute($dto);

    $dto2 = new \App\Modules\Inventory\Application\DTOs\StockOperationDTO(
        productId:   $product->id,
        warehouseId: $wh->id,
        quantity:    15,
        reason:      'Second batch',
    );

    $inv = $useCase->execute($dto2);

    expect($inv->quantity)->toBe(25);
});

// ── DispatchStockUseCase ──────────────────────────────────────────────────────

test('DispatchStockUseCase decrements quantity', function () {
    $receive  = app(ReceiveStockUseCase::class);
    $dispatch = app(DispatchStockUseCase::class);
    $product  = Product::factory()->create();
    $wh       = Warehouse::factory()->create();

    $base = new \App\Modules\Inventory\Application\DTOs\StockOperationDTO(
        productId: $product->id, warehouseId: $wh->id, quantity: 30, reason: 'receive',
    );
    $receive->execute($base);

    $out = new \App\Modules\Inventory\Application\DTOs\StockOperationDTO(
        productId: $product->id, warehouseId: $wh->id, quantity: 10, reason: 'dispatch',
    );
    $inv = $dispatch->execute($out);

    expect($inv->quantity)->toBe(20);
    $this->assertDatabaseHas('stock_movements', ['type' => 'out', 'quantity' => 10]);
});

test('DispatchStockUseCase throws InsufficientStockException', function () {
    $dispatch = app(DispatchStockUseCase::class);
    $product  = Product::factory()->create();
    $wh       = Warehouse::factory()->create();

    $dto = new \App\Modules\Inventory\Application\DTOs\StockOperationDTO(
        productId: $product->id, warehouseId: $wh->id, quantity: 50, reason: 'too many',
    );

    expect(fn () => $dispatch->execute($dto))
        ->toThrow(InsufficientStockException::class);
});

// ── AdjustStockUseCase ────────────────────────────────────────────────────────

test('AdjustStockUseCase sets absolute quantity and records delta', function () {
    $receive = app(ReceiveStockUseCase::class);
    $adjust  = app(AdjustStockUseCase::class);
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    $receive->execute(new \App\Modules\Inventory\Application\DTOs\StockOperationDTO(
        productId: $product->id, warehouseId: $wh->id, quantity: 30, reason: 'base',
    ));

    $inv = $adjust->execute(new \App\Modules\Inventory\Application\DTOs\StockOperationDTO(
        productId: $product->id, warehouseId: $wh->id, quantity: 20, reason: 'inventory count',
    ));

    expect($inv->quantity)->toBe(20);
    $this->assertDatabaseHas('stock_movements', [
        'type'     => 'adjustment',
        'quantity' => -10,  // delta: 20 - 30
    ]);
});

// ── TransferStockUseCase ──────────────────────────────────────────────────────

test('TransferStockUseCase moves stock between warehouses', function () {
    $receive  = app(ReceiveStockUseCase::class);
    $transfer = app(TransferStockUseCase::class);
    $product  = Product::factory()->create();
    $src      = Warehouse::factory()->create();
    $dst      = Warehouse::factory()->create();

    $receive->execute(new \App\Modules\Inventory\Application\DTOs\StockOperationDTO(
        productId: $product->id, warehouseId: $src->id, quantity: 40, reason: 'seed',
    ));

    $transfer->execute(new \App\Modules\Inventory\Application\DTOs\TransferStockDTO(
        productId:       $product->id,
        fromWarehouseId: $src->id,
        toWarehouseId:   $dst->id,
        quantity:        15,
        reason:          'redistribution',
    ));

    $this->assertDatabaseHas('inventory', ['warehouse_id' => $src->id, 'quantity' => 25]);
    $this->assertDatabaseHas('inventory', ['warehouse_id' => $dst->id, 'quantity' => 15]);

    // Two movement rows recorded
    expect(StockMovement::where('type', 'transfer')->count())->toBe(2);
});

test('TransferStockUseCase throws on insufficient source stock', function () {
    $transfer = app(TransferStockUseCase::class);
    $product  = Product::factory()->create();
    $src      = Warehouse::factory()->create();
    $dst      = Warehouse::factory()->create();

    $dto = new \App\Modules\Inventory\Application\DTOs\TransferStockDTO(
        productId:       $product->id,
        fromWarehouseId: $src->id,
        toWarehouseId:   $dst->id,
        quantity:        100,
        reason:          'too many',
    );

    expect(fn () => $transfer->execute($dto))
        ->toThrow(InsufficientStockException::class);
});

// ── ReserveStockUseCase ───────────────────────────────────────────────────────

test('ReserveStockUseCase increments reserved_quantity', function () {
    $receive = app(ReceiveStockUseCase::class);
    $reserve = app(ReserveStockUseCase::class);
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    $receive->execute(new \App\Modules\Inventory\Application\DTOs\StockOperationDTO(
        productId: $product->id, warehouseId: $wh->id, quantity: 20, reason: 'seed',
    ));

    $inv = $reserve->reserve($product->id, null, $wh->id, 8);
    expect($inv->reserved_quantity)->toBe(8)
        ->and($inv->availableQuantity())->toBe(12);
});

test('ReserveStockUseCase throws when not enough available', function () {
    $reserve = app(ReserveStockUseCase::class);
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    expect(fn () => $reserve->reserve($product->id, null, $wh->id, 50))
        ->toThrow(InsufficientStockException::class);
});

test('ReserveStockUseCase release decrements reserved_quantity safely', function () {
    $receive = app(ReceiveStockUseCase::class);
    $reserve = app(ReserveStockUseCase::class);
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    $receive->execute(new \App\Modules\Inventory\Application\DTOs\StockOperationDTO(
        productId: $product->id, warehouseId: $wh->id, quantity: 20, reason: 'seed',
    ));
    $reserve->reserve($product->id, null, $wh->id, 10);

    $inv = $reserve->release($product->id, null, $wh->id, 10);
    expect($inv->reserved_quantity)->toBe(0);
});

test('ReserveStockUseCase release does not go below zero', function () {
    $reserve = app(ReserveStockUseCase::class);
    $product = Product::factory()->create();
    $wh      = Warehouse::factory()->create();

    // No prior reservation; release should be a no-op
    $inv = $reserve->release($product->id, null, $wh->id, 99);
    expect($inv->reserved_quantity)->toBe(0);
});

// ── CheckLowStockUseCase ──────────────────────────────────────────────────────

test('CheckLowStockUseCase summary returns correct counts', function () {
    $checker = app(CheckLowStockUseCase::class);
    $product = Product::factory()->create();

    Inventory::create(['product_id' => $product->id, 'warehouse_id' => Warehouse::factory()->create()->id, 'quantity' => 3,  'reserved_quantity' => 0]);
    Inventory::create(['product_id' => $product->id, 'warehouse_id' => Warehouse::factory()->create()->id, 'quantity' => 0,  'reserved_quantity' => 0]);
    Inventory::create(['product_id' => $product->id, 'warehouse_id' => Warehouse::factory()->create()->id, 'quantity' => 50, 'reserved_quantity' => 0]);

    $summary = $checker->summary(5);
    expect($summary['low_stock_count'])->toBe(1)   // qty=3 only (qty=0 not > 0)
        ->and($summary['out_of_stock_count'])->toBe(1)
        ->and($summary['threshold'])->toBe(5);
});
