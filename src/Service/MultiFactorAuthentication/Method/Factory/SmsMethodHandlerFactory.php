<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\Factory;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\Client\SmsClientInterface;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\SmsMethodHandler;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\TimeBasedOneTimePasswordVerification;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class SmsMethodHandlerFactory
{
    public function __construct(
        private readonly TimeBasedOneTimePasswordVerification $timeBasedOneTimePasswordVerification,
        private readonly SmsClientInterface $smsClient,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function create(UserMfaMethod $userMfaMethod): SmsMethodHandler
    {
        return new SmsMethodHandler(
            $userMfaMethod,
            $this->timeBasedOneTimePasswordVerification,
            $this->smsClient,
            $this->parameterBag,
        );
    }
}
