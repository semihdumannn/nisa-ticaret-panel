<?php

namespace App\Modules\Product\Application\DTOs;

readonly class CreateBrandDTO
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public bool $isActive = true,
        public int $sortOrder = 0,
    ) {}
}
