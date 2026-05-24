<?php

namespace App\Modules\Notification\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Application\UseCases\RegisterDeviceTokenUseCase;
use App\Modules\Notification\Domain\Contracts\FcmTokenRepositoryInterface;
use App\Modules\Notification\Presentation\API\Requests\RegisterDeviceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function __construct(
        private readonly FcmTokenRepositoryInterface $tokenRepo,
    ) {}

    /**
     * POST /api/v1/devices
     */
    public function register(RegisterDeviceRequest $request, RegisterDeviceTokenUseCase $useCase): JsonResponse
    {
        $v = $request->validated();

        $useCase->execute(
            $request->user()->id,
            $v['token'],
            $v['platform'] ?? null,
        );

        return response()->json(['message' => 'Device registered.'], 201);
    }

    /**
     * DELETE /api/v1/devices
     * Body: { token: string }
     */
    public function unregister(Request $request): JsonResponse
    {
        $token = $request->input('token');

        if (! $token) {
            return response()->json(['message' => 'Token is required.'], 422);
        }

        $this->tokenRepo->unregister($request->user()->id, $token);

        return response()->json(['message' => 'Device unregistered.']);
    }
}
