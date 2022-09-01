<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;

final class U2fMethodHandler implements MfaMethodHandler
{
    public function __construct(
        /* @phpstan-ignore-next-line */
        private readonly UserMfaMethod $userMfaMethod,
    ) {
    }

    public function verifyAuthentication(array $verificationData): bool
    {
        throw new \LogicException('Not implemented yet');
    }

    public function sendOtp(UserMfaMethod $userMfaMethod): void
    {
        throw new \LogicException('Not implemented yet');
    }
}
