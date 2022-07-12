<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use Coddin\IdentityProvider\Repository\UserRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\UserEntity;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;

final class IdentityRepository implements IdentityProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @throws OAuthEntityNotFoundException
     */
    public function getUserEntityByIdentifier(mixed $identifier): UserEntity
    {
        if (!is_string($identifier)) {
            throw new \LogicException(
                'User identifier should be a string; The OAuth library does not use strict typing, hence the `mixed` property',
            );
        }

        $user = $this->userRepository->getOneByUuid($identifier);

        return new UserEntity($user);
    }
}
