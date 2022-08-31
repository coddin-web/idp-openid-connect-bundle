<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository;

use Coddin\IdentityProvider\Entity\OpenIDConnect\Enum\MfaMethod as MfaMethodIdentifier;
use Coddin\IdentityProvider\Entity\OpenIDConnect\MfaMethod;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;

interface UserMfaMethodRepository
{
    public function getActiveMfaMethodForUser(User $user): UserMfaMethod;

    public function getUnConfiguredMfaMethodForUser(
        User $user,
        MfaMethodIdentifier $mfaMethod,
    ): UserMfaMethod;

    public function deleteForUserById(
        int $userMfaMethodId,
        User $user,
    ): void;

    /**
     * @return array<UserMfaMethod>
     */
    public function getAllStaleMethodsByType(
        User $user,
        MfaMethodIdentifier $mfaMethod,
    ): array;

    public function removeAllStaleMethodsByType(
        User $user,
        MfaMethodIdentifier $mfaMethod,
    ): void;

    public function initialize(
        User $user,
        MfaMethod $mfaMethodEntity,
    ): UserMfaMethod;

    public function setValidated(UserMfaMethod $userMfaMethod): void;

    public function setActive(UserMfaMethod $userMfaMethod): void;
}
