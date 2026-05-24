<?php

namespace App\Modules\Campaign\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Campaign\Application\UseCases\GetActiveCampaignsUseCase;
use App\Modules\Campaign\Presentation\API\Resources\CampaignResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * GET /api/v1/campaigns
     * Return all currently active campaigns (optionally filtered by product).
     */
    public function index(Request $request, GetActiveCampaignsUseCase $useCase): JsonResponse
    {
        $productId = $request->integer('product_id') ?: null;
        $campaigns = $useCase->execute($productId);

        return response()->json([
            'data' => CampaignResource::collection($campaigns),
        ]);
    }
}
