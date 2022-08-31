<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\TimeBasedOneTimePasswordVerification;

final class AuthenticatorAppMethodHandler implements MfaMethodHandler
{
    public function __construct(
        private readonly UserMfaMethod $userMfaMethod,
        private readonly TimeBasedOneTimePasswordVerification $timeBasedOneTimePasswordVerification,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function verifyAuthentication(array $verificationData): bool
    {
        return $this->timeBasedOneTimePasswordVerification->verifyAuthentication(
            userMfaMethod: $this->userMfaMethod,
            verificationData: $verificationData,
        );
    }
}
