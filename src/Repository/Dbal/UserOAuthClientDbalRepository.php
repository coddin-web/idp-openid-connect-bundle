<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserOAuthClient;
use Coddin\IdentityProvider\Repository\UserOAuthClientRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserOAuthClient>
 */
final class UserOAuthClientDbalRepository extends ServiceEntityRepository implements UserOAuthClientRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($registry, UserOAuthClient::class);
    }

    public function getOneByUserReferenceAndExternalId(string $userReference, string $externalId): UserOAuthClient
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('user_oauth_client')
            ->from(UserOAuthClient::class, 'user_oauth_client')
            ->innerJoin('user_oauth_client.user', 'user')
            ->innerJoin('user_oauth_client.oAuthClient', 'oauth_client')
            ->where('user.uuid = :userReference')
            ->andWhere('oauth_client.externalId = :externalId')
            ->setParameter('userReference', $userReference)
            ->setParameter('externalId', $externalId);

        try {
            /** @var \Coddin\IdentityProvider\Entity\OpenIDConnect\UserOAuthClient $userOauthClient */
            $userOauthClient = $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException | NonUniqueResultException) {
            throw OAuthEntityNotFoundException::fromClassNameAndExternalId(
                OAuthClient::class,
                $externalId,
            );
        }

        return $userOauthClient;
    }
}
