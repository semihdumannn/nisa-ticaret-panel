<?php

namespace App\Modules\User\Infrastructure\External;

use App\Modules\User\Domain\Contracts\TotpServiceInterface;
use PragmaRX\Google2FA\Google2FA;

class Google2FaTotpService implements TotpServiceInterface
{
    public function __construct(private readonly Google2FA $google2fa) {}

    /**
     * {@inheritDoc}
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * {@inheritDoc}
     */
    public function verify(string $secret, string $code, int $window = 10): bool
    {
        return $this->google2fa->verifyKey($secret, $code, $window);
    }

    /**
     * {@inheritDoc}
     */
    public function currentTimestamp(): int
    {
        return now()->timestamp;
    }
}
