<?php

namespace App\Modules\Order\Application\UseCases;

use App\Models\AppConfig;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Modules\Campaign\Application\DTOs\ApplyCouponDTO;
use App\Modules\Campaign\Application\UseCases\ValidateCouponUseCase;
use App\Modules\Campaign\Domain\Contracts\CouponRepositoryInterface;
use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Modules\Notification\Domain\Events\OrderPlacedEvent;
use App\Modules\Order\Application\DTOs\CreateOrderDTO;
use App\Modules\Order\Domain\Contracts\CartRepositoryInterface;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Exceptions\EmptyCartException;
use App\Modules\Order\Domain\Exceptions\MinimumOrderAmountException;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Illuminate\Support\Facades\DB;

class CreateOrderUseCase
{
    public function __construct(
        private readonly CartRepositoryInterface      $cartRepo,
        private readonly OrderRepositoryInterface     $orderRepo,
        private readonly InventoryRepositoryInterface $inventoryRepo,
        private readonly ValidateCouponUseCase        $validateCoupon,
        private readonly CouponRepositoryInterface    $couponRepo,
    ) {}

    public function execute(CreateOrderDTO $dto): Order
    {
        // ── Items kaynağını belirle ───────────────────────────────────────────
        $cart = null;

        if (!empty($dto->items)) {
            // Doğrudan items gönderildi — cart'ı atla
            $cartItems = collect($dto->items)->map(function ($i) {
                return (object)[
                    'product_id' => $i['product_id'],
                    'variant_id' => $i['variant_id'] ?? null,
                    'quantity'   => $i['quantity'],
                    'product'    => Product::findOrFail($i['product_id']),
                    'variant'    => isset($i['variant_id']) && $i['variant_id']
                                    ? ProductVariant::find($i['variant_id'])
                                    : null,
                ];
            });
        } else {
            // Fallback: server cart'tan al
            $cart = $this->cartRepo->getOrCreate($dto->userId);
            $cart->load('items.product', 'items.variant');
            if ($cart->items->isEmpty()) {
                throw new EmptyCartException();
            }
            $cartItems = $cart->items;
        }

        // ── Coupon validation ─────────────────────────────────────────────────
        $coupon         = null;
        $couponDiscount = 0.0;

        if ($dto->couponCode) {
            $estimatedSubtotal = $cartItems->sum(
                fn ($i) => (float) ($i->variant?->effectivePrice() ?? $i->product->price) * $i->quantity
            );
            $coupon = $this->validateCoupon->execute(new ApplyCouponDTO(
                code:     $dto->couponCode,
                userId:   $dto->userId,
                subtotal: $estimatedSubtotal,
            ));
        }

        // Minimum sipariş tutarı kontrolü
        $estimatedSubtotal = $cartItems->sum(
            fn ($i) => (float) ($i->variant?->effectivePrice() ?? $i->product->price) * $i->quantity
        );
        $minAmount = (float) AppConfig::get('min_order_amount', 50.0);
        if ($estimatedSubtotal < $minAmount) {
            throw new MinimumOrderAmountException(
                minimum: $minAmount,
                actual:  $estimatedSubtotal,
            );
        }

        $loaded = DB::transaction(function () use ($dto, $cart, $cartItems, $coupon, &$couponDiscount) {
            // ── Stok kontrol ──────────────────────────────────────────────────
            $reservations = [];
            foreach ($cartItems as $cartItem) {
                $best = $this->bestInventory(
                    $cartItem->product_id,
                    $cartItem->variant_id,
                    $cartItem->quantity,
                );
                $reservations[] = ['inventory' => $best, 'qty' => $cartItem->quantity];
            }

            // ── Sipariş kalemleri hazırla ─────────────────────────────────────
            $subtotal      = 0.0;
            $taxTotal      = 0.0;
            $orderItemData = [];

            foreach ($cartItems as $cartItem) {
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

            if ($coupon) {
                $couponDiscount = $coupon->calculateDiscount($subtotal);
            }

            $total = round(max(0, $subtotal + $taxTotal - $couponDiscount), 2);

            // ── Order oluştur ─────────────────────────────────────────────────
            $order = $this->orderRepo->create([
                'order_number'    => 'TEMP',
                'customer_id'     => $dto->userId,
                'address_id'      => $dto->addressId,
                'status'          => OrderStatus::PENDING->value,
                'subtotal'        => $subtotal,
                'discount_amount' => $couponDiscount,
                'coupon_id'       => $coupon?->id,
                'tax_amount'      => $taxTotal,
                'shipping_amount' => 0,
                'total'           => $total,
                'payment_method'  => $dto->paymentMethod,
                'payment_status'  => 'pending',
                'notes'           => $dto->notes,
                'created_by'      => $dto->userId,
            ]);

            $order->update([
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($orderItemData as $itemData) {
                $order->items()->create($itemData);
            }

            // ── Stok rezerve et ───────────────────────────────────────────────
            foreach ($reservations as $res) {
                $res['inventory']->increment('reserved_quantity', $res['qty']);
            }

            // ── Kupon kullanımı ───────────────────────────────────────────────
            if ($coupon) {
                $this->couponRepo->recordUsage($coupon->id, $dto->userId, $order->id);
                $this->couponRepo->incrementUsage($coupon->id);
            }

            // ── Geçmiş & cart temizle ─────────────────────────────────────────
            $this->orderRepo->addHistory($order, OrderStatus::PENDING->value, 'Order placed.', $dto->userId);

            if ($cart) {
                $this->cartRepo->clear($cart);
            }

            return $order->load('items.product', 'address');
        });

        // Event transaction DIŞINDA — hata siparişi iptal etmez
        try {
            event(new OrderPlacedEvent($loaded));
        } catch (\Throwable) {}

        return $loaded;
    }

    private function bestInventory(int $productId, ?int $variantId, int $needed): Inventory
    {
        // Tek sorguda variant-specific + product-level kayıtları al
        $candidates = Inventory::where('product_id', $productId)
            ->where(function ($q) use ($variantId) {
                $q->whereNull('variant_id');
                if ($variantId) {
                    $q->orWhere('variant_id', $variantId);
                }
            })
            ->get();

        // Variant-specific tercih et, yoksa product-level'a dön
        $best = null;
        if ($variantId) {
            $best = $candidates->firstWhere('variant_id', $variantId);
        }
        if (! $best) {
            $best = $candidates->firstWhere('variant_id', null);
        }

        // Stok kontrolü PHP'de — whereRaw yok, plan cache sorunu yok
        if ($best && ($best->quantity - $best->reserved_quantity) >= $needed) {
            return $best;
        }

        $available = (int) $candidates->sum(fn ($i) => max(0, $i->quantity - $i->reserved_quantity));

        throw new InsufficientStockException(
            requested: $needed,
            available: $available,
        );
    }
}
