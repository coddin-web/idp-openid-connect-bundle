<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use Coddin\IdentityProvider\Generator\OAuthAccessTokenCreate;
use Coddin\OpenIDConnect\Domain\Repository\OAuthAccessTokenRepository;
use Coddin\OpenIDConnect\Domain\Repository\OAuthClientRepository;
use Coddin\OpenIDConnect\Domain\Repository\UserRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\Factory\AccessTokenEntityFactory;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

final class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly OAuthClientRepository $oauthClientRepository,
        private readonly OAuthAccessTokenRepository $oauthAccessTokenRepository,
        private readonly AccessTokenEntityFactory $accessTokenEntityFactory,
        private readonly OAuthAccessTokenCreate $oauthAccessTokenCreate,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        $userIdentifier = null,
    ): AccessTokenEntityInterface {
        $accessToken = $this->accessTokenEntityFactory->create();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        if (!is_string($userIdentifier) && !is_int($userIdentifier) && $userIdentifier !== null) {
            throw new \LogicException('The userIdentifier is typed as mixed but should only be string|int|null');
        }

        $accessToken->setUserIdentifier($userIdentifier);

        return $accessToken;
    }

    /**
     * @throws OAuthEntityNotFoundException
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $oauthUserIdentifier = $accessTokenEntity->getUserIdentifier();

        if (!is_string($oauthUserIdentifier)) {
            throw new \LogicException(
                'Incorrect usage of the AccessTokenEntity, an identifier should always be a string',
            );
        }

        $user = $this->userRepository->getOneByUuid($oauthUserIdentifier);
        $oauthClient = $this->oauthClientRepository->getOneByExternalId(
            $accessTokenEntity->getClient()->getIdentifier(),
        );

        $oauthAccessToken = $this->oauthAccessTokenCreate->create(
            $accessTokenEntity->getIdentifier(),
            $accessTokenEntity->getExpiryDateTime(),
            $user,
            $oauthClient,
        );

        $this->entityManager->persist($oauthAccessToken);
        $this->entityManager->flush();
    }

    /**
     * @inheritdoc
     * @throws OAuthEntityNotFoundException
     */
    public function revokeAccessToken($tokenId): void
    {
        $accessToken = $this->oauthAccessTokenRepository->getOneByExternalId($tokenId);

        $this->oauthAccessTokenRepository->revoke($accessToken);
    }

    /**
     * @inheritdoc
     * @throws OAuthEntityNotFoundException
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        $accessToken = $this->oauthAccessTokenRepository->getOneByExternalId($tokenId);

        return $accessToken->getRevokedAt() !== null;
    }
}
