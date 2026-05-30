<?php

namespace App\Modules\Order\Application\UseCases;

use App\Models\Address;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\User;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Support\Facades\DB;

class CreateFieldAgentOrderUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepo,
    ) {}

    /**
     * Create an order directly from an items array (no cart required).
     * Used by field agents from the saha terminali.
     *
     * @param array{
     *   agent_id: int,
     *   customer_id: ?int,
     *   customer_name: ?string,
     *   customer_phone: ?string,
     *   items: array<array{product_id:int, variant_id:?int, quantity:int}>,
     *   address_id: ?int,
     *   address_text: ?string,
     *   payment_method: ?string,
     *   notes: ?string,
     *   time_slot: ?string,
     * } $data
     * @throws InsufficientStockException
     */
    public function execute(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // ── 1. Resolve customer ───────────────────────────────────────────
            $customerId = $data['customer_id'] ?? null;

            if (! $customerId && ! empty($data['customer_phone'])) {
                $customer = User::firstOrCreate(
                    ['phone' => $data['customer_phone']],
                    [
                        'name'      => $data['customer_name'] ?? 'Müşteri',
                        'role'      => 'customer',
                        'is_active' => true,
                    ]
                );
                $customerId = $customer->id;
            }

            // ── 2. Resolve address ────────────────────────────────────────────
            $addressId = $data['address_id'] ?? null;

            if (! $addressId && ! empty($data['address_text']) && $customerId) {
                $address = Address::create([
                    'user_id'      => $customerId,
                    'title'        => 'Saha Terminali',
                    'full_address' => $data['address_text'],
                    'is_default'   => false,
                ]);
                $addressId = $address->id;
            }

            // ── 3. Build order items + check stock ────────────────────────────
            $reservations  = [];
            $orderItemData = [];
            $subtotal      = 0.0;
            $taxTotal      = 0.0;

            foreach ($data['items'] as $item) {
                $productId = $item['product_id'];
                $variantId = $item['variant_id'] ?? null;
                $qty       = (int) $item['quantity'];

                $best = Inventory::where('product_id', $productId)
                    ->where('variant_id', $variantId)
                    ->whereRaw('(quantity - reserved_quantity) >= ?', [$qty])
                    ->orderByRaw('(quantity - reserved_quantity) DESC')
                    ->first();

                if (! $best) {
                    $available = (int) Inventory::where('product_id', $productId)
                        ->where('variant_id', $variantId)
                        ->sum(DB::raw('GREATEST(0, quantity - reserved_quantity)'));

                    throw new InsufficientStockException(requested: $qty, available: $available);
                }

                $reservations[] = ['inventory' => $best, 'qty' => $qty];

                $product   = $best->product ?? \App\Models\Product::find($productId);
                $variant   = $variantId ? \App\Models\ProductVariant::find($variantId) : null;
                $unitPrice = (float) ($variant?->effectivePrice() ?? $product->price);
                $taxRate   = (float) ($product->tax_rate ?? 0);

                $subtotal += $unitPrice * $qty;
                $taxTotal += ($unitPrice * $taxRate / 100) * $qty;

                $orderItemData[] = [
                    'product_id'      => $productId,
                    'variant_id'      => $variantId,
                    'product_name'    => $variant ? "{$product->name} – {$variant->name}" : $product->name,
                    'quantity'        => $qty,
                    'unit_price'      => $unitPrice,
                    'tax_rate'        => $taxRate,
                    'discount_amount' => 0,
                    'total'           => round($unitPrice * (1 + $taxRate / 100) * $qty, 2),
                ];
            }

            $subtotal = round($subtotal, 2);
            $taxTotal = round($taxTotal, 2);
            $total    = round($subtotal + $taxTotal, 2);

            $notes = $data['notes'] ?? null;
            if (! empty($data['time_slot'])) {
                $notes = trim("Zaman dilimi: {$data['time_slot']}" . ($notes ? "\n{$notes}" : ''));
            }

            // ── 4. Create order ───────────────────────────────────────────────
            $order = $this->orderRepo->create([
                'order_number'    => 'TEMP',
                'customer_id'     => $customerId,
                'address_id'      => $addressId,
                'status'          => OrderStatus::PENDING->value,
                'subtotal'        => $subtotal,
                'discount_amount' => 0,
                'tax_amount'      => $taxTotal,
                'shipping_amount' => 0,
                'total'           => $total,
                'payment_method'  => $data['payment_method'] ?? 'cash',
                'payment_status'  => 'pending',
                'notes'           => $notes,
                'source'          => 'field_agent',
                'created_by'      => $data['agent_id'],
            ]);

            $order->update([
                'order_number' => 'FA-' . now()->format('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($orderItemData as $itemData) {
                $order->items()->create($itemData);
            }

            // ── 5. Reserve stock ──────────────────────────────────────────────
            foreach ($reservations as $res) {
                $res['inventory']->increment('reserved_quantity', $res['qty']);
            }

            $this->orderRepo->addHistory($order, OrderStatus::PENDING->value, 'Saha terminali siparişi.', $data['agent_id']);

            return $order->load('items.product', 'address');
        });
    }
}
