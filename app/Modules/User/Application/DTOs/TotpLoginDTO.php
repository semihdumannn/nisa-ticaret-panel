<?php

namespace App\Modules\User\Application\DTOs;

readonly class TotpLoginDTO
{
    public function __construct(
        public string $phone,
        public string $code,
        public ?string $deviceName = null,
    ) {}
}
