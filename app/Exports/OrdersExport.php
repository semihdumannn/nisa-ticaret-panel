<?php

namespace App\Exports;

use App\Models\Order;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use App\Modules\Order\Domain\ValueObjects\PaymentStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly ?string $status = null,
        private readonly ?string $fromDate = null,
        private readonly ?string $toDate = null,
    ) {}

    public function query()
    {
        $q = Order::with(['customer', 'address', 'items'])
            ->orderByDesc('created_at');

        if ($this->status) {
            $q->where('status', $this->status);
        }

        if ($this->fromDate) {
            $q->where('created_at', '>=', $this->fromDate . ' 00:00:00');
        }

        if ($this->toDate) {
            $q->where('created_at', '<=', $this->toDate . ' 23:59:59');
        }

        return $q;
    }

    public function headings(): array
    {
        return [
            'Order #',
            'Customer',
            'Phone',
            'Status',
            'Payment',
            'Items',
            'Subtotal (₺)',
            'Discount (₺)',
            'Total (₺)',
            'City',
            'District',
            'Placed At',
            'Delivered At',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->customer?->name ?? '—',
            $order->customer?->phone ?? '—',
            OrderStatus::tryFrom($order->status)?->label() ?? $order->status,
            PaymentStatus::tryFrom($order->payment_status)?->label() ?? $order->payment_status,
            $order->items->count(),
            number_format((float) $order->subtotal, 2),
            number_format((float) $order->discount_amount, 2),
            number_format((float) $order->total, 2),
            $order->address?->city ?? '—',
            $order->address?->district ?? '—',
            $order->created_at?->format('d.m.Y H:i'),
            $order->delivered_at?->format('d.m.Y H:i') ?? '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
