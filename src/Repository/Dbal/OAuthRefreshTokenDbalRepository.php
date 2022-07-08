<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthRefreshToken;
use Coddin\OpenIDConnect\Domain\Repository\OAuthRefreshTokenRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OAuthRefreshToken>
 */
final class OAuthRefreshTokenDbalRepository extends ServiceEntityRepository implements OAuthRefreshTokenRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct(registry: $registry, entityClass: OAuthRefreshToken::class);
    }

    public function getOneByExternalId(string $externalId): OAuthRefreshToken
    {
        /** @var OAuthRefreshToken|null $refreshToken */
        $refreshToken = $this->findOneBy(['externalId' => $externalId]);

        if (!$refreshToken instanceof OAuthRefreshToken) {
            throw OAuthEntityNotFoundException::fromClassNameAndExternalId(
                OAuthRefreshToken::class,
                $externalId,
            );
        }

        return $refreshToken;
    }

    public function revoke(OAuthRefreshToken $refreshToken): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $query = $queryBuilder
            ->update(OAuthRefreshToken::class, 'oauth_refresh_token')
            ->set('oauth_refresh_token.revokedAt', ':revokedAt')
            ->where('oauth_refresh_token.id = :refreshTokenId')
            ->setParameter('revokedAt', new \DateTimeImmutable())
            ->setParameter('refreshTokenId', $refreshToken->getId())
            ->getQuery();

        $query->execute();
    }
}
