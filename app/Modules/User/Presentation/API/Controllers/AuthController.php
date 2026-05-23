<?php

namespace App\Modules\User\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Application\DTOs\FirebaseLoginDTO;
use App\Modules\User\Application\UseCases\FirebaseLoginUseCase;
use App\Modules\User\Domain\Exceptions\InvalidFirebaseTokenException;
use App\Modules\User\Presentation\API\Requests\FirebaseLoginRequest;
use App\Modules\User\Presentation\API\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/firebase-login
     *
     * FirebaseLoginUseCase injected via method injection so unused routes
     * (logout, me) do not eagerly resolve Firebase dependencies.
     */
    public function firebaseLogin(FirebaseLoginRequest $request, FirebaseLoginUseCase $loginUseCase): JsonResponse
    {
        try {
            $result = $loginUseCase->execute(
                new FirebaseLoginDTO(
                    idToken:    $request->validated('id_token'),
                    deviceName: $request->validated('device_name') ?? ($request->userAgent() ?? 'mobile'),
                ),
            );

            $user = $result['user']->load('profile');

            return response()->json([
                'message'     => $result['is_new'] ? 'Account created and logged in.' : 'Logged in successfully.',
                'token'       => $result['token'],
                'token_type'  => 'Bearer',
                'user'        => new UserResource($user),
                'is_new_user' => $result['is_new'],
            ], $result['is_new'] ? 201 : 200);
        } catch (InvalidFirebaseTokenException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => 'INVALID_TOKEN',
            ], 401);
        }
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
