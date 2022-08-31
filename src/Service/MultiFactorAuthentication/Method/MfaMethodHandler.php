<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method;

interface MfaMethodHandler
{
    /**
     * @param array<string, mixed> $verificationData
     */
    public function verifyAuthentication(array $verificationData): bool;
}
