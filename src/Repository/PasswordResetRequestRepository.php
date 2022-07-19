<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository;

use Coddin\IdentityProvider\Entity\OpenIDConnect\PasswordResetRequest;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Exception\PasswordResetEntityNotFoundException;

interface PasswordResetRequestRepository
{
    /**
     * @throws PasswordResetEntityNotFoundException
     */
    public function getValidPasswordResetRequest(User $user, string $token): PasswordResetRequest;

    /**
     * @throws PasswordResetEntityNotFoundException
     */
    public function getUserForResetToken(string $token): User;

    public function invalidateTokenForUser(User $user, string $token): void;
}
