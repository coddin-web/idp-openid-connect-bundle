<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use Coddin\IdentityProvider\Generator\OAuthRefreshTokenCreate;
use Coddin\IdentityProvider\Repository\OAuthAccessTokenRepository;
use Coddin\IdentityProvider\Repository\OAuthRefreshTokenRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\RefreshTokenEntity;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OAuthRefreshTokenCreate $oauthRefreshTokenCreate,
        private readonly OAuthAccessTokenRepository $oauthAccessTokenRepository,
        private readonly OAuthRefreshTokenRepository $oauthRefreshTokenRepository,
    ) {
    }

    /**
     * @inheritdoc
     * @throws OAuthEntityNotFoundException
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $accessToken = $this->oauthAccessTokenRepository->getOneByExternalId(
            externalId: $refreshTokenEntity->getAccessToken()->getIdentifier(),
        );

        $refreshToken = $this->oauthRefreshTokenCreate->create(
            externalId: $refreshTokenEntity->getIdentifier(),
            expiresAt: $refreshTokenEntity->getExpiryDateTime(),
            oauthAccessToken: $accessToken,
        );

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();
    }

    /**
     * @inheritdoc
     * @throws OAuthEntityNotFoundException
     */
    public function revokeRefreshToken($tokenId): void
    {
        $refreshToken = $this->oauthRefreshTokenRepository->getOneByExternalId($tokenId);

        $this->oauthRefreshTokenRepository->revoke($refreshToken);
    }

    /**
     * @inheritdoc
     * @throws OAuthEntityNotFoundException
     */
    public function isRefreshTokenRevoked($tokenId): bool
    {
        $refreshToken = $this->oauthRefreshTokenRepository->getOneByExternalId($tokenId);

        return $refreshToken->getRevokedAt() !== null;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function getNewRefreshToken(): RefreshTokenEntity
    {
        return new RefreshTokenEntity();
    }
}
