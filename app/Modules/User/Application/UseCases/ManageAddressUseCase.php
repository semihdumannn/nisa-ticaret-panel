<?php

namespace App\Modules\User\Application\UseCases;

use App\Models\Address;
use App\Models\User;
use App\Modules\User\Application\DTOs\CreateAddressDTO;

class ManageAddressUseCase
{
    public function create(User $user, CreateAddressDTO $dto): Address
    {
        if ($dto->isDefault) {
            // Unset any existing default
            $user->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        return $user->addresses()->create([
            'title'        => $dto->title,
            'full_address' => $dto->fullAddress,
            'district'     => $dto->district,
            'city'         => $dto->city,
            'postal_code'  => $dto->postalCode,
            'latitude'     => $dto->latitude,
            'longitude'    => $dto->longitude,
            'is_default'   => $dto->isDefault,
        ]);
    }

    public function update(Address $address, CreateAddressDTO $dto): Address
    {
        if ($dto->isDefault && ! $address->is_default) {
            $address->user->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        $address->update([
            'title'        => $dto->title,
            'full_address' => $dto->fullAddress,
            'district'     => $dto->district,
            'city'         => $dto->city,
            'postal_code'  => $dto->postalCode,
            'latitude'     => $dto->latitude,
            'longitude'    => $dto->longitude,
            'is_default'   => $dto->isDefault,
        ]);

        return $address->fresh();
    }

    public function setDefault(User $user, Address $address): Address
    {
        $user->addresses()->where('is_default', true)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return $address->fresh();
    }

    public function delete(Address $address): void
    {
        $address->delete();
    }
}
