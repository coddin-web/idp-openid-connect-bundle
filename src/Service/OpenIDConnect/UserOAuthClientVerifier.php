<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\OpenIDConnect;

use Coddin\OpenIDConnect\Domain\Repository\UserOAuthClientRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;

final class UserOAuthClientVerifier
{
    public function __construct(
        private readonly UserOAuthClientRepository $userOAuthClientRepository,
    ) {
    }

    /**
     * @throws OAuthEntityNotFoundException
     */
    public function verify(ClientEntityInterface $client, UserEntityInterface $user): void
    {
        // This line is ignored because the actual Client passed *can* contain the EntityTrait which has a
        // 'getIdentifier' which returns mixed.
        /* @phpstan-ignore-next-line */
        if (!\is_string($client->getIdentifier())) {
            throw new \LogicException('The Client Identifier must always be a string');
        }

        if (!\is_string($user->getIdentifier())) {
            throw new \LogicException('The User Identifier must always be a string');
        }

        $this->userOAuthClientRepository->getOneByUserReferenceAndExternalId(
            userReference: $user->getIdentifier(),
            externalId: $client->getIdentifier(),
        );
    }
}
