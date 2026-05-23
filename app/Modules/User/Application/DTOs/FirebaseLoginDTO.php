<?php

namespace App\Modules\User\Application\DTOs;

readonly class FirebaseLoginDTO
{
    public function __construct(
        public string $idToken,
        public ?string $deviceName = null,
    ) {}
}
