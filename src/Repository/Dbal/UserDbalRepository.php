<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Generator\UserCreate;
use Coddin\IdentityProvider\Generator\UserOAuthClientCreate;
use Coddin\IdentityProvider\Repository\UserRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Safe\Exceptions\JsonException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
final class UserDbalRepository extends ServiceEntityRepository implements UserRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
        parent::__construct($registry, User::class);
    }

    /**
     * @param array<int, string> $roles
     * @throws JsonException
     */
    public function create(
        string $username,
        string $password,
        string $email,
        array $roles = ['ROLE_USER'],
    ): User {
        $user = UserCreate::create(
            username: $username,
            email: $email,
            password: $password,
            roles: $roles,
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function findOneByUsername(
        string $username,
    ): ?User {
        /** @var User|null $user */
        $user = $this->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    public function getOneById(int $id): User
    {
        /** @var User|null $user */
        $user = $this->findOneBy(['id' => $id]);

        if (!$user instanceof User) {
            throw OAuthEntityNotFoundException::fromClassNameAndId(
                User::class,
                $id,
            );
        }

        return $user;
    }

    public function getOneByUuid(string $uuid): User
    {
        /** @var User|null $user */
        $user = $this->findOneBy(['uuid' => $uuid]);

        if (!$user instanceof User) {
            throw OAuthEntityNotFoundException::fromClassNameAndUuid(
                User::class,
                $uuid,
            );
        }

        return $user;
    }

    public function assignToOAuthClients(
        User $user,
        OAuthClient ...$oauthClients,
    ): void {
        foreach ($oauthClients as $oauthClient) {
            $userOAuthClient = UserOAuthClientCreate::create(
                user: $user,
                oauthClient: $oauthClient,
            );

            $this->entityManager->persist($userOAuthClient);
        }

        $this->entityManager->flush();
    }

    public function updatePassword(
        User $user,
        string $password,
    ): void {
        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $user,
            $password,
        );

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->update(User::class, 'user')
            ->set('user.password', ':password')
            ->set('user.updatedAt', ':updatedAt')
            ->where('user.id = :userId')
            ->setParameter('password', $hashedPassword)
            ->setParameter('userId', $user->getId())
            ->setParameter('updatedAt', new \DateTimeImmutable());

        $queryBuilder->getQuery()->execute();
    }
}
