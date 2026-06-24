<?php

namespace App\Modules\Subscription\Application\UseCases;

use App\Models\Order;
use App\Modules\Notification\Infrastructure\Jobs\SendPushNotificationJob;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Subscription\Domain\Contracts\SubscriptionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessDueSubscriptionsUseCase
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepo,
    ) {}

    public function execute(): array
    {
        $subscriptions   = $this->subscriptionRepo->findDueToday();
        $processedCount  = 0;
        $skippedCount    = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $variant = $subscription->variant;

                // ── Stock check ───────────────────────────────────────────────
                if ($variant->stock < $subscription->quantity) {
                    Log::warning("Subscription #{$subscription->id} skipped: insufficient stock.", [
                        'subscription_id' => $subscription->id,
                        'user_id'         => $subscription->user_id,
                        'stock'           => $variant->stock,
                        'required'        => $subscription->quantity,
                    ]);

                    dispatch(new SendPushNotificationJob(
                        userId: $subscription->user_id,
                        title:  'Stok yetersiz',
                        body:   'Stok yetersiz — abonelik siparişi oluşturulamadı',
                        data:   ['subscription_id' => (string) $subscription->id],
                    ));

                    $skippedCount++;
                    continue;
                }

                // ── Price calculation ─────────────────────────────────────────
                $unitPrice      = $variant->effectivePrice();
                $discountedUnit = $unitPrice * (1 - $subscription->discount_rate / 100);
                $discountedTotal = round($discountedUnit * $subscription->quantity, 2);

                // ── Create order ──────────────────────────────────────────────
                $order = Order::create([
                    'order_number'   => 'TEMP',
                    'customer_id'    => $subscription->user_id,
                    'address_id'     => $subscription->address_id,
                    'status'         => OrderStatus::PENDING->value,
                    'subtotal'       => $discountedTotal,
                    'discount_amount' => 0,
                    'tax_amount'     => 0,
                    'shipping_amount' => 0,
                    'total'          => $discountedTotal,
                    'payment_method' => 'subscription',
                    'payment_status' => 'pending',
                    'notes'          => "Abonelik #{$subscription->id} — otomatik sipariş",
                ]);

                $order->update([
                    'order_number' => 'SUB-' . now()->format('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                ]);

                $order->items()->create([
                    'product_id'   => $subscription->product_id,
                    'variant_id'   => $subscription->variant_id,
                    'product_name' => $subscription->product->name,
                    'quantity'     => $subscription->quantity,
                    'unit_price'   => (float) $discountedUnit,
                    'tax_rate'     => 0,
                    'total'        => $discountedTotal,
                ]);

                // ── Advance subscription date ─────────────────────────────────
                $subscription->update([
                    'last_order_id'   => $order->id,
                    'next_order_date' => $this->advanceDate($subscription->plan, $subscription->next_order_date),
                ]);

                // ── FCM notification ──────────────────────────────────────────
                dispatch(new SendPushNotificationJob(
                    userId: $subscription->user_id,
                    title:  'Abonelik siparişi oluşturuldu',
                    body:   'Abonelik siparişiniz oluşturuldu',
                    data:   [
                        'order_id'        => (string) $order->id,
                        'subscription_id' => (string) $subscription->id,
                    ],
                ));

                $processedCount++;

            } catch (\Throwable $e) {
                Log::error("Subscription #{$subscription->id} processing failed: {$e->getMessage()}", [
                    'subscription_id' => $subscription->id,
                    'exception'       => $e,
                ]);
            }
        }

        return ['processed' => $processedCount, 'skipped' => $skippedCount];
    }

    private function advanceDate(string $plan, Carbon $currentDate): Carbon
    {
        return match ($plan) {
            'weekly'   => $currentDate->addDays(7),
            'biweekly' => $currentDate->addDays(14),
            'monthly'  => $currentDate->addMonthNoOverflow(),
        };
    }
}
