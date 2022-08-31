<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\MfaMethod;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Doctrine\Common\Collections\ArrayCollection;

final class UserMfaMethodCreate
{
    public function create(
        User $user,
        MfaMethod $mfaMethod,
    ): UserMfaMethod {
        return new UserMfaMethod(
            false,
            false,
            $user,
            $mfaMethod,
            new ArrayCollection(),
        );
    }
}
