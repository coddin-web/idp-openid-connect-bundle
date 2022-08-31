<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\Factory;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\AuthenticatorAppMethodHandler;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\TimeBasedOneTimePasswordVerification;

final class AuthenticatorAppMethodHandlerFactory
{
    public function __construct(
        private readonly TimeBasedOneTimePasswordVerification $timeBasedOneTimePasswordVerification,
    ) {
    }

    public function create(UserMfaMethod $userMfaMethod): AuthenticatorAppMethodHandler
    {
        return new AuthenticatorAppMethodHandler(
            userMfaMethod: $userMfaMethod,
            timeBasedOneTimePasswordVerification: $this->timeBasedOneTimePasswordVerification,
        );
    }
}
