<?php

namespace App\Modules\Product\Application\UseCases;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadProductImageUseCase
{
    public function execute(Product $product, UploadedFile $file, bool $isPrimary = false): ProductImage
    {
        // If this is the first image or explicitly primary, unset others
        $hasImages = $product->images()->exists();
        if ($isPrimary || ! $hasImages) {
            $isPrimary = true;
            if ($hasImages) {
                $product->images()->update(['is_primary' => false]);
            }
        }

        $extension = $file->getClientOriginalExtension();
        $filename  = 'products/' . $product->id . '/' . Str::random(12) . '.' . $extension;
        $file->storeAs('', $filename, 'public');

        $imageUrl  = asset('storage/' . $filename);
        $sortOrder = $product->images()->max('sort_order') + 1;

        return $product->images()->create([
            'image_url'  => $imageUrl,
            'is_primary' => $isPrimary,
            'sort_order' => $sortOrder,
        ]);
    }
}
