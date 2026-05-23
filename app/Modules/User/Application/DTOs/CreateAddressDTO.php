<?php

namespace App\Modules\User\Application\DTOs;

readonly class CreateAddressDTO
{
    public function __construct(
        public string $fullAddress,
        public ?string $title = null,
        public ?string $district = null,
        public ?string $city = null,
        public ?string $postalCode = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public bool $isDefault = false,
    ) {}
}
