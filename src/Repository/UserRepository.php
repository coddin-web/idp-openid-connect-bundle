<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;

interface UserRepository
{
    /**
     * @param array<int, string> $roles
     */
    public function create(string $username, string $password, string $email, array $roles): User;

    /**
     * @throws OAuthEntityNotFoundException
     */
    public function getOneById(int $id): User;

    /**
     * @throws OAuthEntityNotFoundException
     */
    public function getOneByUuid(string $uuid): User;

    public function findOneByUsername(string $username): ?User;

    public function assignToOAuthClients(User $user, OAuthClient ...$oauthClients): void;
}
