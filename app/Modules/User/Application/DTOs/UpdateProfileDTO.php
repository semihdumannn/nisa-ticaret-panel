<?php

namespace App\Modules\User\Application\DTOs;

readonly class UpdateProfileDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $companyName = null,
        public ?string $taxNumber = null,
    ) {}
}
