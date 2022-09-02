<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethodConfig;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\Client\SmsClientInterface;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\TimeBasedOneTimePasswordVerification;
use OTPHP\TOTP;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class SmsMethodHandler implements MfaMethodHandler
{
    private const TOTP_PERIOD = (5 * 60);

    public function __construct(
        private readonly UserMfaMethod $userMfaMethod,
        private readonly TimeBasedOneTimePasswordVerification $timeBasedOneTimePasswordVerification,
        private readonly SmsClientInterface $smsClient,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function verifyAuthentication(array $verificationData): bool
    {
        $verificationData['period'] = self::TOTP_PERIOD;

        return $this->timeBasedOneTimePasswordVerification->verifyAuthentication(
            userMfaMethod: $this->userMfaMethod,
            verificationData: $verificationData,
        );
    }

    public function sendOtp(UserMfaMethod $userMfaMethod): void
    {
        $userMfaMethodConfigs = $userMfaMethod->getUserMfaMethodConfigs();

        // Todo: Move key/value logic to a service/helper that handles getting the correct value.
        /** @var UserMfaMethodConfig $userMfaMethodPhoneNumber */
        $userMfaMethodPhoneNumber = $userMfaMethodConfigs->filter(
            fn(UserMfaMethodConfig $userMfaMethodConfig) => $userMfaMethodConfig->getKey() === 'phone_number',
        )->first();
        /** @var UserMfaMethodConfig $userMfaMethodSecret */
        $userMfaMethodSecret = $userMfaMethodConfigs->filter(
            fn(UserMfaMethodConfig $userMfaMethodConfig) => $userMfaMethodConfig->getKey() === 'totp_secret_key',
        )->first();

        $oneTimePassword = TOTP::create(
            secret: $userMfaMethodSecret->getValue(),
            period: self::TOTP_PERIOD,
        );

        $this->smsClient->sendTextMessage(
            /* @phpstan-ignore-next-line */
            originator: $this->parameterBag->get('idp.company_name'),
            recipient: $userMfaMethodPhoneNumber->getValue(),
            // Todo? Better / bigger message? Translatable?
            body: $oneTimePassword->now(),
        );
    }
}
