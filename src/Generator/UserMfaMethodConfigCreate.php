<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethodConfig;

final class UserMfaMethodConfigCreate
{
    public function create(
        string $key,
        string $value,
        UserMfaMethod $userMfaMethod,
    ): UserMfaMethodConfig {
        return new UserMfaMethodConfig(
            key: $key,
            value: $value,
            userMfaMethod: $userMfaMethod,
        );
    }
}
