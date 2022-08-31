<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Entity\OpenIDConnect\Enum\MfaMethod as MfaMethodIdentifier;
use Coddin\IdentityProvider\Entity\OpenIDConnect\MfaMethod;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Coddin\IdentityProvider\Exception\UserMfaMethodNotFoundException;
use Coddin\IdentityProvider\Generator\UserMfaMethodCreate;
use Coddin\IdentityProvider\Repository\UserMfaMethodRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserMfaMethod>
 */
final class UserMfaMethodDbalRepository extends ServiceEntityRepository implements UserMfaMethodRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserMfaMethodCreate $userMfaMethodCreate,
    ) {
        parent::__construct($registry, UserMfaMethod::class);
    }

    /**
     * @throws UserMfaMethodNotFoundException
     */
    public function getActiveMfaMethodForUser(User $user): UserMfaMethod
    {
        $userMfaMethod = $this->findOneBy([
            'user' => $user,
            'isActive' => true,
            'isValidated' => true,
        ]);

        if (!$userMfaMethod instanceof UserMfaMethod) {
            throw UserMfaMethodNotFoundException::activeNotFound();
        }

        return $userMfaMethod;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getUnConfiguredMfaMethodForUser(
        User $user,
        MfaMethodIdentifier $mfaMethod,
    ): UserMfaMethod {
        $queryBuilder = $this->createQueryBuilder('umm');
        $queryBuilder
            ->leftJoin(MfaMethod::class, 'mm', Join::WITH, 'mm.identifier = :mfaMethod')
            ->where('umm.user = :user')
            ->andWhere('umm.isValidated = :isValidated')
            ->andWhere('umm.isActive = :isActive')
            ->setParameter('user', $user)
            ->setParameter('isValidated', false)
            ->setParameter('isActive', false)
            ->setParameter('mfaMethod', $mfaMethod->value);

        /* @phpstan-ignore-next-line */
        return $queryBuilder->getQuery()->getSingleResult();
    }

    /**
     * @throws UserMfaMethodNotFoundException
     */
    public function deleteForUserById(
        int $userMfaMethodId,
        User $user,
    ): void {
        $userMfaMethod = $this->findOneBy([
            'id' => $userMfaMethodId,
            'user' => $user,
        ]);

        if ($userMfaMethod === null) {
            throw UserMfaMethodNotFoundException::create('UserMfaMethod can not be deleted because it can not be found.');
        }

        $this->entityManager->remove($userMfaMethod);
        $this->entityManager->flush();
    }

    public function getAllStaleMethodsByType(
        User $user,
        MfaMethodIdentifier $mfaMethod,
    ): array {
        $queryBuilder = $this->createQueryBuilder('umm');
        $queryBuilder
            ->leftJoin(MfaMethod::class, 'mm', Join::WITH, 'mm.identifier = :mfaMethod')
            ->where('umm.user = :user')
            ->andWhere('umm.isValidated = :isValidated')
            ->setParameter('user', $user)
            ->setParameter('isValidated', false)
            ->setParameter('mfaMethod', $mfaMethod->value);

        /* @phpstan-ignore-next-line */
        return $queryBuilder->getQuery()->getResult();
    }

    public function removeAllStaleMethodsByType(
        User $user,
        MfaMethodIdentifier $mfaMethod,
    ): void {
        $userMfaMethods = $this->getAllStaleMethodsByType($user, $mfaMethod);
        foreach ($userMfaMethods as $userMfaMethod) {
            $this->entityManager->remove($userMfaMethod);
        }

        $this->entityManager->flush();
    }

    public function initialize(
        User $user,
        MfaMethod $mfaMethodEntity,
    ): UserMfaMethod {
        $userMfaMethod = $this->userMfaMethodCreate->create($user, $mfaMethodEntity);
        $this->entityManager->persist($userMfaMethod);

        return $userMfaMethod;
    }

    public function setValidated(UserMfaMethod $userMfaMethod): void
    {
        $queryBuilder = $this->createQueryBuilder('umm');
        $queryBuilder
            ->update(UserMfaMethod::class, 'umm')
            ->set('umm.isValidated', true)
            ->where('umm.id = :userMfaMethodId')
            ->setParameter('userMfaMethodId', $userMfaMethod->getId());

        $queryBuilder->getQuery()->execute();
    }

    public function setActive(UserMfaMethod $userMfaMethod): void
    {
        $queryBuilder = $this->createQueryBuilder('umm');
        $queryBuilder
            ->update(UserMfaMethod::class, 'umm')
            ->set('umm.isActive', true)
            ->where('umm.id = :userMfaMethodId')
            ->setParameter('userMfaMethodId', $userMfaMethod->getId());

        $queryBuilder->getQuery()->execute();
    }
}
