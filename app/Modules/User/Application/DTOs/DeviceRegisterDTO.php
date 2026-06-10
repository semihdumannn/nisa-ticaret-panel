<?php

namespace App\Modules\User\Application\DTOs;

readonly class DeviceRegisterDTO
{
    public function __construct(
        public string $phone,
        public ?string $name = null,
        public ?string $deviceName = null,
    ) {}
}
