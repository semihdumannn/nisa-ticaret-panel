<?php

namespace App\Modules\Product\Application\DTOs;

readonly class CreateProductDTO
{
    public function __construct(
        public string $name,
        public float $price,
        public ?int $brandId = null,
        public ?string $sku = null,
        public ?string $description = null,
        public ?string $barcode = null,
        public string $unit = 'piece',
        public ?float $costPrice = null,
        public float $taxRate = 20.00,
        public int $minOrderQty = 1,
        public ?int $maxOrderQty = null,
        public bool $isFeatured = false,
        public bool $isActive = true,
        public ?array $metadata = null,
        public array $categoryIds = [],
    ) {}
}
