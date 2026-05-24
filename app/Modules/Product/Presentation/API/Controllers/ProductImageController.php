<?php

namespace App\Modules\Product\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageController extends Controller
{
    /**
     * GET /api/v1/products/{product}/images
     * List all images for a product.
     */
    public function index(Product $product): JsonResponse
    {
        return response()->json([
            'images' => $product->images()
                ->orderBy('sort_order')
                ->get(['id', 'image_url', 'is_primary', 'sort_order']),
        ]);
    }

    /**
     * POST /api/v1/products/{product}/images
     * Upload one or more images for a product. Admin only.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'images'         => ['required', 'array', 'min:1', 'max:10'],
            'images.*'       => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'primary_index'  => ['nullable', 'integer', 'min:0'],
        ]);

        $primaryIndex  = $request->integer('primary_index', 0);
        $uploaded      = [];
        $currentMaxSort = $product->images()->max('sort_order') ?? -1;

        foreach ($request->file('images') as $index => $file) {
            $extension = $file->getClientOriginalExtension();
            $filename  = 'products/' . $product->id . '/' . Str::random(16) . '.' . $extension;

            Storage::disk(config('filament.default_filesystem_disk', 'public'))->put(
                $filename,
                file_get_contents($file->getRealPath()),
            );

            $isPrimary = ($index === $primaryIndex);

            // If this is marked primary, demote existing primary
            if ($isPrimary) {
                $product->images()->where('is_primary', true)->update(['is_primary' => false]);
            }

            $image = $product->images()->create([
                'image_url'  => Storage::disk(config('filament.default_filesystem_disk', 'public'))->url($filename),
                'is_primary' => $isPrimary,
                'sort_order' => ++$currentMaxSort,
            ]);

            $uploaded[] = [
                'id'         => $image->id,
                'image_url'  => $image->image_url,
                'is_primary' => $image->is_primary,
                'sort_order' => $image->sort_order,
            ];
        }

        return response()->json([
            'message' => count($uploaded) . ' image(s) uploaded successfully.',
            'images'  => $uploaded,
        ], 201);
    }

    /**
     * DELETE /api/v1/products/{product}/images/{image}
     * Delete a product image. Admin only.
     */
    public function destroy(Product $product, ProductImage $image): JsonResponse
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['message' => 'Image does not belong to this product.'], 403);
        }

        // Delete file from storage
        $path = parse_url($image->image_url, PHP_URL_PATH);
        Storage::disk(config('filament.default_filesystem_disk', 'public'))->delete(ltrim($path, '/'));

        $wasPrimary = $image->is_primary;
        $image->delete();

        // Promote next image to primary if deleted was primary
        if ($wasPrimary) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        return response()->json(['message' => 'Image deleted.']);
    }

    /**
     * PUT /api/v1/products/{product}/images/{image}/set-primary
     * Set an image as the primary. Admin only.
     */
    public function setPrimary(Product $product, ProductImage $image): JsonResponse
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['message' => 'Image does not belong to this product.'], 403);
        }

        $product->images()->where('is_primary', true)->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return response()->json(['message' => 'Primary image updated.']);
    }
}
