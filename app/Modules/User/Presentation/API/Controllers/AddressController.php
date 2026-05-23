<?php

namespace App\Modules\User\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Modules\User\Application\DTOs\CreateAddressDTO;
use App\Modules\User\Application\UseCases\ManageAddressUseCase;
use App\Modules\User\Presentation\API\Requests\StoreAddressRequest;
use App\Modules\User\Presentation\API\Resources\AddressResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AddressController extends Controller
{
    public function __construct(private readonly ManageAddressUseCase $manageAddress) {}

    /**
     * GET /api/v1/addresses
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->latest()->get();

        return AddressResource::collection($addresses);
    }

    /**
     * POST /api/v1/addresses
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $v = $request->validated();

        $address = $this->manageAddress->create(
            $request->user(),
            new CreateAddressDTO(
                fullAddress: $v['full_address'],
                title:       $v['title'] ?? null,
                district:    $v['district'] ?? null,
                city:        $v['city'] ?? null,
                postalCode:  $v['postal_code'] ?? null,
                latitude:    isset($v['latitude']) ? (float) $v['latitude'] : null,
                longitude:   isset($v['longitude']) ? (float) $v['longitude'] : null,
                isDefault:   (bool) ($v['is_default'] ?? false),
            ),
        );

        return response()->json([
            'message' => 'Address created successfully.',
            'address' => new AddressResource($address),
        ], 201);
    }

    /**
     * PUT /api/v1/addresses/{address}
     */
    public function update(StoreAddressRequest $request, Address $address): JsonResponse
    {
        // Ensure address belongs to authenticated user
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $v = $request->validated();

        $address = $this->manageAddress->update(
            $address,
            new CreateAddressDTO(
                fullAddress: $v['full_address'],
                title:       $v['title'] ?? null,
                district:    $v['district'] ?? null,
                city:        $v['city'] ?? null,
                postalCode:  $v['postal_code'] ?? null,
                latitude:    isset($v['latitude']) ? (float) $v['latitude'] : null,
                longitude:   isset($v['longitude']) ? (float) $v['longitude'] : null,
                isDefault:   (bool) ($v['is_default'] ?? false),
            ),
        );

        return response()->json([
            'message' => 'Address updated successfully.',
            'address' => new AddressResource($address),
        ]);
    }

    /**
     * DELETE /api/v1/addresses/{address}
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $this->manageAddress->delete($address);

        return response()->json(['message' => 'Address deleted successfully.']);
    }

    /**
     * POST /api/v1/addresses/{address}/set-default
     */
    public function setDefault(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $address = $this->manageAddress->setDefault($request->user(), $address);

        return response()->json([
            'message' => 'Default address updated.',
            'address' => new AddressResource($address),
        ]);
    }
}
