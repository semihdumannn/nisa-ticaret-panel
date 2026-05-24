<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function query()
    {
        return Product::with(['brand', 'categories'])
            ->withCount('variants')
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Name',
            'Brand',
            'Categories',
            'Price (₺)',
            'Cost Price (₺)',
            'Tax Rate (%)',
            'Unit',
            'Variants',
            'Featured',
            'Active',
            'Created At',
        ];
    }

    public function map($product): array
    {
        return [
            $product->sku,
            $product->name,
            $product->brand?->name ?? '—',
            $product->categories->pluck('name')->join(', ') ?: '—',
            number_format((float) $product->price, 2),
            $product->cost_price ? number_format((float) $product->cost_price, 2) : '—',
            (float) $product->tax_rate,
            $product->unit,
            $product->variants_count,
            $product->is_featured ? 'Yes' : 'No',
            $product->is_active ? 'Yes' : 'No',
            $product->created_at?->format('d.m.Y'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
