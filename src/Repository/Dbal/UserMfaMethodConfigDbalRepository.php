<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethodConfig;
use Coddin\IdentityProvider\Generator\UserMfaMethodConfigCreate;
use Coddin\IdentityProvider\Repository\UserMfaMethodConfigRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserMfaMethodConfig>
 */
final class UserMfaMethodConfigDbalRepository extends ServiceEntityRepository implements UserMfaMethodConfigRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserMfaMethodConfigCreate $userMfaMethodConfigCreate,
    ) {
        parent::__construct($registry, UserMfaMethodConfig::class);
    }

    public function initialize(
        UserMfaMethod $userMfaMethod,
        string $configKey,
        string $configValue,
    ): UserMfaMethodConfig {
        $userMfaMethodConfig = $this->userMfaMethodConfigCreate->create(
            key: $configKey,
            value: $configValue,
            userMfaMethod: $userMfaMethod,
        );

        $this->entityManager->persist($userMfaMethodConfig);
        $this->entityManager->flush();

        return $userMfaMethodConfig;
    }
}
