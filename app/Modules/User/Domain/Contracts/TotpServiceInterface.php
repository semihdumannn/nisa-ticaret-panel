<?php

namespace App\Modules\User\Domain\Contracts;

interface TotpServiceInterface
{
    /**
     * Generate a new random Base32 TOTP secret.
     */
    public function generateSecret(): string;

    /**
     * Verify a 6-digit TOTP code against a secret, allowing a time window.
     *
     * @param  int  $window  Number of 30s steps to check before/after current time (±window*30s)
     */
    public function verify(string $secret, string $code, int $window = 10): bool;

    /**
     * Current server unix timestamp (seconds).
     */
    public function currentTimestamp(): int;
}
