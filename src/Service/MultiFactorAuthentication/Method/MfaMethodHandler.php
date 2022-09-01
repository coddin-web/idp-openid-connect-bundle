<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;

interface MfaMethodHandler
{
    /**
     * @param array<string, mixed> $verificationData
     */
    public function verifyAuthentication(array $verificationData): bool;

    public function sendOtp(UserMfaMethod $userMfaMethod): void;
}
