<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Safe\Exceptions\JsonException;

use function Safe\json_encode;

final class UserCreate
{
    /**
     * @param array<string> $roles
     * @throws JsonException
     */
    public static function create(
        string $username,
        string $email,
        string $password,
        array $roles = ['ROLE_USER'],
    ): User {
        return new User(
            uuid: UuidV4::uuid4()->toString(),
            username: $username,
            email: $email,
            password: password_hash($password, PASSWORD_BCRYPT, ['cost' => 15]),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
            oAuthAuthorizationCodes: new ArrayCollection(),
            oAuthAccessTokens: new ArrayCollection(),
            userOAuthClients: new ArrayCollection(),
            roles: json_encode($roles),
        );
    }
}
