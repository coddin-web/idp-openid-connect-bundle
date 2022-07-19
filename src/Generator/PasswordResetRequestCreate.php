<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\PasswordResetRequest;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;

final class PasswordResetRequestCreate
{
    public static function create(
        User $user,
        string $token,
        \DateTimeImmutable $validUntil,
    ): PasswordResetRequest {
        return new PasswordResetRequest(
            $user,
            $token,
            new \DateTimeImmutable(),
            $validUntil,
        );
    }
}
