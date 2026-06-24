<?php

namespace App\Modules\Subscription\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscription\Application\UseCases\CancelSubscriptionUseCase;
use App\Modules\Subscription\Application\UseCases\CreateSubscriptionUseCase;
use App\Modules\Subscription\Application\UseCases\ListSubscriptionsUseCase;
use App\Modules\Subscription\Application\UseCases\UpdateSubscriptionUseCase;
use App\Modules\Subscription\Domain\Exceptions\SubscriptionException;
use App\Modules\Subscription\Presentation\API\Requests\CreateSubscriptionRequest;
use App\Modules\Subscription\Presentation\API\Requests\UpdateSubscriptionRequest;
use App\Modules\Subscription\Presentation\API\Resources\SubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly ListSubscriptionsUseCase $listSubscriptions,
        private readonly CreateSubscriptionUseCase $createSubscription,
        private readonly UpdateSubscriptionUseCase $updateSubscription,
        private readonly CancelSubscriptionUseCase $cancelSubscription,
    ) {}

    /**
     * GET /api/v1/subscriptions
     */
    public function index(Request $request): JsonResponse
    {
        $statuses = match ($request->get('status', 'active_paused')) {
            'active'    => ['active'],
            'paused'    => ['paused'],
            'cancelled' => ['cancelled'],
            'all'       => ['active', 'paused', 'cancelled'],
            default     => ['active', 'paused'],
        };

        $subscriptions = $this->listSubscriptions->execute($request->user()->id, $statuses);

        return response()->json([
            'data' => SubscriptionResource::collection($subscriptions),
        ]);
    }

    /**
     * POST /api/v1/subscriptions
     */
    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        $subscription = $this->createSubscription->execute(
            $request->user()->id,
            $request->validated()
        );

        // Load relations for the resource
        $subscription->load(['product', 'variant', 'address']);

        return response()->json(new SubscriptionResource($subscription), 201, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * PUT /api/v1/subscriptions/{id}
     */
    public function update(UpdateSubscriptionRequest $request, int $id): JsonResponse
    {
        try {
            $subscription = $this->updateSubscription->execute(
                $id,
                $request->user()->id,
                $request->validated()
            );
        } catch (SubscriptionException $e) {
            if ($e->errorCode === 'SUBSCRIPTION_NOT_FOUND') {
                return response()->json(['message' => 'Subscription not found.'], 404);
            }
            throw $e;
        }

        $subscription->load(['product', 'variant', 'address']);

        return response()->json(new SubscriptionResource($subscription));
    }

    /**
     * DELETE /api/v1/subscriptions/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->cancelSubscription->execute($id, $request->user()->id);
        } catch (SubscriptionException $e) {
            if ($e->errorCode === 'SUBSCRIPTION_NOT_FOUND') {
                return response()->json(['message' => 'Subscription not found.'], 404);
            }
            throw $e;
        }

        return response()->json(null, 204);
    }
}
