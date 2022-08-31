<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\OpenIDConnect\Enum;

enum MfaMethod: string
{
    case METHOD_SMS = 'sms';
    case METHOD_EMAIL = 'email';
    case METHOD_AUTHENTICATOR_APP = 'auth_app';
    case METHOD_U2F_KEY = 'u2f_key';

    public static function fromValue(string $value): self
    {
        foreach (self::cases() as $mfaMethod) {
            if ($mfaMethod->value === $value) {
                return $mfaMethod;
            }
        }

        throw new \ValueError(
            sprintf(
                '%s is not a valid backing value for enum %s',
                $value,
                self::class,
            ),
        );
    }
}
