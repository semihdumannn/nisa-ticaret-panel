<?php

namespace App\Modules\Product\Application\UseCases;

use App\Models\Product;
use App\Modules\Product\Application\DTOs\CreateProductDTO;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;

class CreateProductUseCase
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository) {}

    public function execute(CreateProductDTO $dto): Product
    {
        $product = $this->productRepository->create([
            'brand_id'      => $dto->brandId,
            'sku'           => $dto->sku,
            'name'          => $dto->name,
            'description'   => $dto->description,
            'barcode'       => $dto->barcode,
            'unit'          => $dto->unit,
            'price'         => $dto->price,
            'cost_price'    => $dto->costPrice,
            'tax_rate'      => $dto->taxRate,
            'min_order_qty' => $dto->minOrderQty,
            'max_order_qty' => $dto->maxOrderQty,
            'is_featured'   => $dto->isFeatured,
            'is_active'     => $dto->isActive,
            'metadata'      => $dto->metadata,
        ]);

        // Sync categories
        if (! empty($dto->categoryIds)) {
            $product->categories()->sync($dto->categoryIds);
        }

        return $product->load(['brand', 'categories', 'images', 'variants' => fn ($q) => $q->select('product_variants.*')]);
    }
}
