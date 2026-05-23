<?php

namespace App\Modules\User\Domain\Contracts;

interface FirebaseAuthInterface
{
    /**
     * Verify a Firebase ID token and return the decoded payload.
     *
     * @param  string  $idToken  Raw Firebase ID token from client
     * @return array{uid: string, phone_number: string|null, email: string|null, name: string|null}
     *
     * @throws \App\Modules\User\Domain\Exceptions\InvalidFirebaseTokenException
     */
    public function verifyIdToken(string $idToken): array;
}
