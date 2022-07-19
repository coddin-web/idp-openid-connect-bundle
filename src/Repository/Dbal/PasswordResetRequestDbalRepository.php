<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Entity\OpenIDConnect\PasswordResetRequest;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Exception\PasswordResetEntityNotFoundException;
use Coddin\IdentityProvider\Repository\PasswordResetRequestRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetRequest>
 */
final class PasswordResetRequestDbalRepository extends ServiceEntityRepository implements PasswordResetRequestRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct(registry: $registry, entityClass: PasswordResetRequest::class);
    }

    /**
     * @throws PasswordResetEntityNotFoundException
     */
    public function getValidPasswordResetRequest(
        User $user,
        string $token,
    ): PasswordResetRequest {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('password_reset_request')
            ->from(PasswordResetRequest::class, 'password_reset_request')
            ->where('password_reset_request.user = :user')
            ->andWhere('password_reset_request.token = :token')
            ->andWhere('password_reset_request.validUntil >= :now')
            ->andWhere('password_reset_request.usedAt IS NULL')
            ->setParameter('user', $user)
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTimeImmutable());

        try {
            /** @var PasswordResetRequest $passwordResetRequest */
            $passwordResetRequest = $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException | NonUniqueResultException) {
            throw PasswordResetEntityNotFoundException::create();
        }

        return $passwordResetRequest;
    }

    /**
     * @throws PasswordResetEntityNotFoundException
     */
    public function getUserForResetToken(string $token): User
    {
        $passwordResetRequest = $this->findOneBy(['token' => $token]);

        if (!$passwordResetRequest instanceof PasswordResetRequest) {
            throw PasswordResetEntityNotFoundException::create();
        }

        return $passwordResetRequest->getUser();
    }

    public function invalidateTokenForUser(User $user, string $token): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->update(PasswordResetRequest::class, 'password_reset_request')
            ->set('password_reset_request.usedAt', ':usedAt')
            ->where('password_reset_request.user = :user')
            ->andWhere('password_reset_request.token = :token')
            ->setParameter('user', $user)
            ->setParameter('token', $token)
            ->setParameter('usedAt', new \DateTimeImmutable());

        $queryBuilder->getQuery()->execute();
    }
}
