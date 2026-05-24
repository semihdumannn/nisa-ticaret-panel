<?php

namespace App\Modules\Order\Application\UseCases;

use App\Models\Inventory;
use App\Models\Order;
use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Modules\Order\Application\DTOs\CreateOrderDTO;
use App\Modules\Order\Domain\Contracts\CartRepositoryInterface;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Exceptions\EmptyCartException;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Support\Facades\DB;

class CreateOrderUseCase
{
    public function __construct(
        private readonly CartRepositoryInterface      $cartRepo,
        private readonly OrderRepositoryInterface     $orderRepo,
        private readonly InventoryRepositoryInterface $inventoryRepo,
    ) {}

    /**
     * Build an order from the user's cart.
     *
     * Flow (atomic):
     *   1. Load cart + items
     *   2. Validate cart not empty
     *   3. For each item: find best warehouse, check stock
     *   4. Create order + order_items (price snapshots)
     *   5. Reserve stock in best warehouse per item
     *   6. Clear cart
     *   7. Record initial status history
     *
     * @throws EmptyCartException
     * @throws InsufficientStockException
     */
    public function execute(CreateOrderDTO $dto): Order
    {
        $cart = $this->cartRepo->getOrCreate($dto->userId);
        $cart->load('items.product', 'items.variant');

        if ($cart->items->isEmpty()) {
            throw new EmptyCartException();
        }

        return DB::transaction(function () use ($dto, $cart) {
            // ── Stock check & best-warehouse resolution ───────────────────────

            $reservations = []; // [ [inventory, qty], ... ]

            foreach ($cart->items as $cartItem) {
                $best = $this->bestInventory(
                    $cartItem->product_id,
                    $cartItem->variant_id,
                    $cartItem->quantity,
                );

                $reservations[] = ['inventory' => $best, 'qty' => $cartItem->quantity];
            }

            // ── Create Order ──────────────────────────────────────────────────

            $subtotal = 0.0;
            $taxTotal = 0.0;

            $orderItemData = [];

            foreach ($cart->items as $i => $cartItem) {
                $product   = $cartItem->product;
                $unitPrice = (float) ($cartItem->variant?->effectivePrice() ?? $product->price);
                $taxRate   = (float) ($product->tax_rate ?? 0);
                $lineTotal = round($unitPrice * (1 + $taxRate / 100) * $cartItem->quantity, 2);

                $subtotal += $unitPrice * $cartItem->quantity;
                $taxTotal += ($unitPrice * ($taxRate / 100)) * $cartItem->quantity;

                $orderItemData[] = [
                    'product_id'      => $cartItem->product_id,
                    'variant_id'      => $cartItem->variant_id,
                    'product_name'    => $cartItem->variant
                        ? "{$product->name} – {$cartItem->variant->name}"
                        : $product->name,
                    'quantity'        => $cartItem->quantity,
                    'unit_price'      => $unitPrice,
                    'tax_rate'        => $taxRate,
                    'discount_amount' => 0,
                    'total'           => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $taxTotal = round($taxTotal, 2);
            $total    = round($subtotal + $taxTotal, 2);

            $order = $this->orderRepo->create([
                'order_number'   => 'TEMP',       // replaced after ID is known
                'customer_id'    => $dto->userId,
                'address_id'     => $dto->addressId,
                'status'         => OrderStatus::PENDING->value,
                'subtotal'       => $subtotal,
                'discount_amount'=> 0,
                'tax_amount'     => $taxTotal,
                'shipping_amount'=> 0,
                'total'          => $total,
                'payment_method' => $dto->paymentMethod,
                'payment_status' => 'pending',
                'notes'          => $dto->notes,
                'created_by'     => $dto->userId,
            ]);

            $order->update([
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
            ]);

            // Create order items
            foreach ($orderItemData as $itemData) {
                $order->items()->create($itemData);
            }

            // ── Reserve Stock ─────────────────────────────────────────────────

            foreach ($reservations as $res) {
                /** @var Inventory $inv */
                $inv = $res['inventory'];
                $inv->increment('reserved_quantity', $res['qty']);
            }

            // ── Record history & clear cart ───────────────────────────────────

            $this->orderRepo->addHistory($order, OrderStatus::PENDING->value, 'Order placed.', $dto->userId);
            $this->cartRepo->clear($cart);

            return $order->load('items.product', 'address');
        });
    }

    /**
     * Find the inventory row with the most available stock for this product.
     * @throws InsufficientStockException
     */
    private function bestInventory(int $productId, ?int $variantId, int $needed): Inventory
    {
        $best = Inventory::where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->whereRaw('(quantity - reserved_quantity) >= ?', [$needed])
            ->orderByRaw('(quantity - reserved_quantity) DESC')
            ->first();

        if (! $best) {
            // MAX(0, ...) works in both SQLite (testing) and PostgreSQL (production)
            $available = (int) Inventory::where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->sum(DB::raw('MAX(0, quantity - reserved_quantity)'));

            throw new InsufficientStockException(
                requested: $needed,
                available: $available,
            );
        }

        return $best;
    }
}
