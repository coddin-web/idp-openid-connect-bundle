<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use Coddin\IdentityProvider\Generator\OAuthAuthorizationCodeCreate;
use Coddin\OpenIDConnect\Domain\Repository\OAuthAuthorizationCodeRepository;
use Coddin\OpenIDConnect\Domain\Repository\OAuthClientRepository;
use Coddin\OpenIDConnect\Domain\Repository\UserRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\AuthCodeEntity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly OAuthClientRepository $oauthClientRepository,
        private readonly OAuthAuthorizationCodeRepository $oauthAuthorizationCodeRepository,
    ) {
    }

    /**
     * @throws OAuthEntityNotFoundException
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $oauthUserIdentifier = $authCodeEntity->getUserIdentifier();

        if (!is_string($oauthUserIdentifier)) {
            throw new \LogicException('Incorrect usage of the AuthCodeEntity, an identifier should always be a string');
        }

        $user = $this->userRepository->getOneByUuid($oauthUserIdentifier);
        $oauthClient = $this->oauthClientRepository->getOneByExternalId($authCodeEntity->getClient()->getIdentifier());

        if ($authCodeEntity->getRedirectUri() === null) {
            throw new \LogicException('The redirectUri cannot be null');
        }

        $oauthAuthorizationCode = OAuthAuthorizationCodeCreate::create(
            identifier: $authCodeEntity->getIdentifier(),
            redirectUri: $authCodeEntity->getRedirectUri(),
            expiresAt: $authCodeEntity->getExpiryDateTime(),
            user: $user,
            oauthClient: $oauthClient,
        );

        $this->entityManager->persist($oauthAuthorizationCode);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function revokeAuthCode($codeId): void
    {
        try {
            $authorizationCode = $this->oauthAuthorizationCodeRepository->getOneByExternalId($codeId);
            $this->oauthAuthorizationCodeRepository->revoke($authorizationCode);
        } catch (OAuthEntityNotFoundException $e) {
            throw new Exception('Failed to revoke authorization code', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws OAuthEntityNotFoundException
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        $authorizationCode = $this->oauthAuthorizationCodeRepository->getOneByExternalId($codeId);

        return $authorizationCode->getRevokedAt() !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode(): AuthCodeEntity
    {
        return new AuthCodeEntity();
    }
}
