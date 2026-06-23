<?php

namespace App\Modules\Favorite\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Favorite\Application\UseCases\AddFavoriteUseCase;
use App\Modules\Favorite\Application\UseCases\ListFavoritesUseCase;
use App\Modules\Favorite\Application\UseCases\RemoveFavoriteUseCase;
use App\Modules\Favorite\Presentation\API\Resources\FavoriteResource;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(
        private readonly ListFavoritesUseCase $listFavorites,
        private readonly AddFavoriteUseCase $addFavorite,
        private readonly RemoveFavoriteUseCase $removeFavorite,
    ) {}

    /**
     * GET /api/v1/favorites
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = $this->listFavorites->execute(
            $request->user()->id,
            (int) $request->get('per_page', 20)
        );

        return response()->json([
            'data' => FavoriteResource::collection($favorites),
            'meta' => [
                'current_page' => $favorites->currentPage(),
                'per_page'     => $favorites->perPage(),
                'total'        => $favorites->total(),
                'last_page'    => $favorites->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/favorites
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(['product_id' => ['required', 'integer', 'exists:products,id']]);

        try {
            $favorite = $this->addFavorite->execute($request->user()->id, (int) $request->product_id);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['error' => 'ALREADY_FAVORITED'], 409);
        }

        return response()->json(new FavoriteResource($favorite), 201);
    }

    /**
     * DELETE /api/v1/favorites/by-product/{productId}
     */
    public function destroyByProduct(Request $request, int $productId): JsonResponse
    {
        $this->removeFavorite->executeByProduct($request->user()->id, $productId);

        return response()->json(null, 204);
    }

    /**
     * DELETE /api/v1/favorites/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $removed = $this->removeFavorite->executeById($id, $request->user()->id);

        if (! $removed) {
            return response()->json(['error' => 'NOT_FOUND'], 404);
        }

        return response()->json(null, 204);
    }
}
