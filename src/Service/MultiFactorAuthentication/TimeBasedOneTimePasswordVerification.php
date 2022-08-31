<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\MultiFactorAuthentication;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use OTPHP\TOTP;

final class TimeBasedOneTimePasswordVerification
{
    /**
     * @param array<string, mixed> $verificationData
     */
    public function verifyAuthentication(
        UserMfaMethod $userMfaMethod,
        array $verificationData,
    ): bool {
        // TODO: No array and no hardcoded strings.
        if (!isset($verificationData['otp'])) {
            throw new \InvalidArgumentException(
                sprintf('Missing key `%s` in verification data.', 'otp'),
            );
        }

        $otp = $verificationData['otp'];

        $secret = null;
        foreach ($userMfaMethod->getUserMfaMethodConfigs() as $userMfaMethodConfig) {
            // Todo: remove hardcoded string.
            if ($userMfaMethodConfig->getKey() === 'totp_secret_key') {
                $secret = $userMfaMethodConfig->getValue();
                break;
            }
        }

        /* @phpstan-ignore-next-line */
        return (TOTP::create($secret))->verify($otp);
    }
}
