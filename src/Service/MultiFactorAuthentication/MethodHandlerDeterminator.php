<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\MultiFactorAuthentication;

use Coddin\IdentityProvider\Entity\OpenIDConnect\Enum\MfaMethod;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\EmailMethodHandler;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\Factory\AuthenticatorAppMethodHandlerFactory;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\Factory\SmsMethodHandlerFactory;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\MfaMethodHandler;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\U2fMethodHandler;

final class MethodHandlerDeterminator
{
    public function __construct(
        private readonly AuthenticatorAppMethodHandlerFactory $authenticatorAppMethodHandlerFactory,
        private readonly SmsMethodHandlerFactory $smsMethodHandlerFactory,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function execute(UserMfaMethod $userMfaMethod): MfaMethodHandler
    {
        return match ($userMfaMethod->getMfaMethod()->getIdentifier()) {
            MfaMethod::METHOD_SMS->value => $this->smsMethodHandlerFactory->create($userMfaMethod),
             // TODO: Implement.
            MfaMethod::METHOD_EMAIL->value => new EmailMethodHandler($userMfaMethod),
            MfaMethod::METHOD_AUTHENTICATOR_APP->value => $this->authenticatorAppMethodHandlerFactory->create($userMfaMethod),
             // TODO: Implement.
            MfaMethod::METHOD_U2F_KEY->value => new U2fMethodHandler($userMfaMethod),
            default => throw new \Exception(
                sprintf(
                    'Unknown MFA method `%s`.',
                    $userMfaMethod->getMfaMethod()->getIdentifier(),
                ),
            ),
        };
    }
}
