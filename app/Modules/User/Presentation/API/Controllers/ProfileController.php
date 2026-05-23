<?php

namespace App\Modules\User\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Application\DTOs\UpdateProfileDTO;
use App\Modules\User\Application\UseCases\UpdateProfileUseCase;
use App\Modules\User\Application\UseCases\UploadAvatarUseCase;
use App\Modules\User\Presentation\API\Requests\UpdateProfileRequest;
use App\Modules\User\Presentation\API\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UpdateProfileUseCase $updateProfileUseCase,
        private readonly UploadAvatarUseCase $uploadAvatarUseCase,
    ) {}

    /**
     * GET /api/v1/profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');

        return response()->json(['user' => new UserResource($user)]);
    }

    /**
     * PUT /api/v1/profile
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $this->updateProfileUseCase->execute(
            $request->user(),
            new UpdateProfileDTO(
                name:        $validated['name'] ?? null,
                email:       $validated['email'] ?? null,
                companyName: $validated['company_name'] ?? null,
                taxNumber:   $validated['tax_number'] ?? null,
            ),
        );

        $user->load('profile');

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => new UserResource($user),
        ]);
    }

    /**
     * POST /api/v1/profile/avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'], // 5 MB max
        ]);

        $user = $this->uploadAvatarUseCase->execute(
            $request->user(),
            $request->file('avatar'),
        );

        return response()->json([
            'message'    => 'Avatar uploaded successfully.',
            'avatar_url' => $user->profile?->avatar_url,
        ]);
    }
}
