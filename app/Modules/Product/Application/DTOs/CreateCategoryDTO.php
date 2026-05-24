<?php

namespace App\Modules\Product\Application\DTOs;

readonly class CreateCategoryDTO
{
    public function __construct(
        public string $name,
        public ?int $parentId = null,
        public ?string $icon = null,
        public ?string $color = null,
        public ?string $description = null,
        public bool $isActive = true,
        public int $sortOrder = 0,
    ) {}
}
