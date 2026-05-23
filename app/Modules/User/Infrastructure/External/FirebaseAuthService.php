<?php

namespace App\Modules\User\Infrastructure\External;

use App\Modules\User\Domain\Contracts\FirebaseAuthInterface;
use App\Modules\User\Domain\Exceptions\InvalidFirebaseTokenException;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Throwable;

class FirebaseAuthService implements FirebaseAuthInterface
{
    public function __construct(private readonly FirebaseAuth $auth) {}

    /**
     * {@inheritDoc}
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            $verifiedToken = $this->auth->verifyIdToken($idToken);
            $claims        = $verifiedToken->claims();

            return [
                'uid'          => $claims->get('sub'),
                'phone_number' => $claims->get('phone_number'),
                'email'        => $claims->get('email'),
                'name'         => $claims->get('name'),
            ];
        } catch (FailedToVerifyToken $e) {
            throw new InvalidFirebaseTokenException('Firebase token verification failed: ' . $e->getMessage());
        } catch (Throwable $e) {
            throw new InvalidFirebaseTokenException('Token verification error: ' . $e->getMessage());
        }
    }
}
