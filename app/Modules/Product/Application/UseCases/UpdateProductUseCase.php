<?php

namespace App\Modules\Product\Application\UseCases;

use App\Models\Product;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;

class UpdateProductUseCase
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository) {}

    public function execute(Product $product, array $data): Product
    {
        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids']);

        $this->productRepository->update($product, $data);

        if ($categoryIds !== null) {
            $product->categories()->sync($categoryIds);
        }

        return $product->fresh(['brand', 'categories', 'images', 'variants']);
    }
}
