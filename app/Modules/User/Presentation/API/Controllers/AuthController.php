<?php

namespace App\Modules\User\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Application\DTOs\DeviceRegisterDTO;
use App\Modules\User\Application\DTOs\TotpLoginDTO;
use App\Modules\User\Application\UseCases\DeviceRegisterUseCase;
use App\Modules\User\Application\UseCases\TotpLoginUseCase;
use App\Modules\User\Domain\Exceptions\InvalidTotpException;
use App\Modules\User\Presentation\API\Requests\DeviceRegisterRequest;
use App\Modules\User\Presentation\API\Requests\TotpLoginRequest;
use App\Modules\User\Presentation\API\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/device-register
     *
     * Telefon numarasına bağlı TOTP secret üretir/döndürür ve Sanctum token verir.
     * Telefon sahiplik doğrulaması yapılmaz (bilinçli tasarım kararı).
     */
    public function deviceRegister(DeviceRegisterRequest $request, DeviceRegisterUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute(
            new DeviceRegisterDTO(
                phone:      $request->validated('phone'),
                name:       $request->validated('name'),
                deviceName: $request->validated('device_name') ?? ($request->userAgent() ?? 'mobile'),
            ),
        );

        $user = $result['user']->load('profile');

        return response()->json([
            'message'     => $result['is_new'] ? 'Account created and logged in.' : 'Logged in successfully.',
            'token'       => $result['token'],
            'token_type'  => 'Bearer',
            'user'        => new UserResource($user),
            'totp_secret' => $result['totp_secret'],
            'totp_period' => 30,
            'is_new_user' => $result['is_new'],
        ], $result['is_new'] ? 201 : 200);
    }

    /**
     * POST /api/v1/auth/totp-login
     */
    public function totpLogin(TotpLoginRequest $request, TotpLoginUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute(
                new TotpLoginDTO(
                    phone:      $request->validated('phone'),
                    code:       $request->validated('code'),
                    deviceName: $request->validated('device_name') ?? ($request->userAgent() ?? 'mobile'),
                ),
            );

            $user = $result['user']->load('profile');

            return response()->json([
                'token'      => $result['token'],
                'token_type' => 'Bearer',
                'user'       => new UserResource($user),
            ]);
        } catch (InvalidTotpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => 'INVALID_TOTP',
            ], 401);
        }
    }

    /**
     * GET /api/v1/auth/server-time
     */
    public function serverTime(): JsonResponse
    {
        return response()->json([
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
}
