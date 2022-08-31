<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethodConfig;

interface UserMfaMethodConfigRepository
{
    public function initialize(
        UserMfaMethod $userMfaMethod,
        string $configKey,
        string $configValue,
    ): UserMfaMethodConfig;
}
