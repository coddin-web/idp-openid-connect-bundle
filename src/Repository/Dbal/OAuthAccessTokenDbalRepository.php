<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Collection\OAuthAccessTokenCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAccessToken;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\OpenIDConnect\Domain\Repository\OAuthAccessTokenRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OAuthAccessToken>
 */
final class OAuthAccessTokenDbalRepository extends ServiceEntityRepository implements OAuthAccessTokenRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct(registry: $registry, entityClass: OAuthAccessToken::class);
    }

    /**
     * @inheritDoc
     */
    public function getOneByExternalId(string $externalId): OAuthAccessToken
    {
        /** @var OAuthAccessToken|null $accessToken */
        $accessToken = $this->findOneBy(['externalId' => $externalId]);

        if (!$accessToken instanceof OAuthAccessToken) {
            throw OAuthEntityNotFoundException::fromClassNameAndExternalId(
                OAuthAccessToken::class,
                $externalId,
            );
        }

        return $accessToken;
    }

    public function findAllActiveForUser(User $user): OAuthAccessTokenCollection
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('oauth_access_token')
            ->from(OAuthAccessToken::class, 'oauth_access_token')
            ->where('oauth_access_token.user = :user')
            ->andWhere('oauth_access_token.revokedAt IS NULL')
            ->andWhere('oauth_access_token.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable());

        /** @var array<OAuthAccessToken> $accessTokens */
        $accessTokens = $queryBuilder->getQuery()->getResult();

        return OAuthAccessTokenCollection::create($accessTokens);
    }

    public function revoke(OAuthAccessToken $accessToken): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->update(OAuthAccessToken::class, 'oauth_access_token')
            ->set('oauth_access_token.revokedAt', ':revokedAt')
            ->where('oauth_access_token.id = :accessTokenId')
            ->setParameter('revokedAt', new \DateTimeImmutable())
            ->setParameter('accessTokenId', $accessToken->getId());

        $queryBuilder->getQuery()->execute();
    }

    public function revokeAllActiveForUser(User $user): void
    {
        $accessTokens = $this->findAllActiveForUser($user);
        foreach ($accessTokens->all() as $accessToken) {
            $this->revoke($accessToken);
        }
    }
}
