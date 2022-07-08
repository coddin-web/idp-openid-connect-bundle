<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Collection\OAuthAuthorizationCodeCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAuthorizationCode;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\OpenIDConnect\Domain\Repository\OAuthAuthorizationCodeRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OAuthAuthorizationCode>
 */
final class OAuthAuthorizationCodeDbalRepository extends ServiceEntityRepository implements OAuthAuthorizationCodeRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($registry, OAuthAuthorizationCode::class);
    }

    public function getOneByExternalId(string $externalId): OAuthAuthorizationCode
    {
        /** @var OAuthAuthorizationCode|null $authorizationCode */
        $authorizationCode = $this->findOneBy(['externalId' => $externalId]);

        if (!$authorizationCode instanceof OAuthAuthorizationCode) {
            throw OAuthEntityNotFoundException::fromClassNameAndExternalId(
                OAuthAuthorizationCode::class,
                $externalId,
            );
        }

        return $authorizationCode;
    }

    public function findAllActiveForUser(User $user): OAuthAuthorizationCodeCollection
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('oauth_auth_code')
            ->from(OAuthAuthorizationCode::class, 'oauth_auth_code')
            ->where('oauth_auth_code.user = :user')
            ->andWhere('oauth_auth_code.revokedAt IS NULL')
            ->andWhere('oauth_auth_code.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable());

        /** @var array<OAuthAuthorizationCode> $authorizationCodes */
        $authorizationCodes = $queryBuilder->getQuery()->getResult();

        return OAuthAuthorizationCodeCollection::create($authorizationCodes);
    }

    public function revoke(OAuthAuthorizationCode $authorizationCode): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $query = $queryBuilder
            ->update(OAuthAuthorizationCode::class, 'oauth_auth_code')
            ->set('oauth_auth_code.revokedAt', ':revokedAt')
            ->where('oauth_auth_code.id = :id')
            ->setParameter('revokedAt', new \DateTimeImmutable())
            ->setParameter('id', $authorizationCode->getId())
            ->getQuery();

        $query->execute();
    }

    public function revokeAllActiveForUser(User $user): void
    {
        $authorizationCodes = $this->findAllActiveForUser($user);
        foreach ($authorizationCodes->all() as $authorizationCode) {
            $this->revoke($authorizationCode);
        }
    }
}
