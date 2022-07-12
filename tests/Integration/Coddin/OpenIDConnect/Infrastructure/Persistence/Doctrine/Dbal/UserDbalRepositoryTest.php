<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace Tests\Integration\Coddin\OpenIDConnect\Infrastructure\Persistence\Doctrine\Dbal;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Generator\OAuthClientCreate;
use Coddin\IdentityProvider\DataFixtures\Data\User as DataUser;
use Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserDbalRepositoryTest extends KernelTestCase
{
    private UserDbalRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /* @phpstan-ignore-next-line */
        $this->repository = $kernel->getContainer()
            ->get(UserDbalRepository::class);
        /* @phpstan-ignore-next-line */
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * @test
     * @covers \Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository::create
     */
    public function create_a_user(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->repository->create(
            username: 'username.dbaltest',
            password: 'password',
            email: 'foo@bar.org',
        );

        /** @var \Coddin\IdentityProvider\Entity\OpenIDConnect\User $user */
        $user = $this->repository->findOneByUsername('username.dbaltest');

        self::assertEquals('foo@bar.org', $user->getEmail());
        /** @noinspection PhpUnhandledExceptionInspection */
        self::assertEquals(['ROLE_USER'], $user->getRoles());
        self::assertTrue(\password_verify('password', $user->getPassword()));
    }

    /**
     * @test
     * @covers \Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository::findOneByUsername
     */
    public function find_one_by_username(): void
    {
        $user = $this->repository->findOneByUsername('non_existing_username');
        self::assertNull($user);

        $user = $this->repository->findOneByUsername(DataUser::UserName->value);
        self::assertInstanceOf(User::class, $user);
    }

    /**
     * @test
     * @covers \Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository::getOneById
     */
    public function get_one_by_id_non_existing(): void
    {
        self::expectException(OAuthEntityNotFoundException::class);
        $this->repository->getOneById(2);
    }

    /**
     * @test
     * @covers \Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository::getOneById
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function get_one_by_id(): void
    {
        $user = $this->repository->getOneById(1);
        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(User::class, $user);
    }

    /**
     * @test
     * @covers \Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository::getOneByUuid
     */
    public function get_one_by_uuid_non_existing(): void
    {
        /** @var \Coddin\IdentityProvider\Entity\OpenIDConnect\User $user */
        $user = $this->repository->findOneByUsername(DataUser::UserName->value);
        $uuid = $user->getUuid();

        self::expectException(OAuthEntityNotFoundException::class);
        $this->repository->getOneByUuid('non_existing_uuid');
    }

    /**
     * @test
     * @covers \Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository::getOneByUuid
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function get_one_by_uuid(): void
    {
        /** @var User $user */
        $user = $this->repository->findOneByUsername(DataUser::UserName->value);
        $uuid = $user->getUuid();

        $user = $this->repository->getOneByUuid($uuid);
        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(User::class, $user);
    }

    /**
     * @test
     * @covers \Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository::assignToOAuthClients
     */
    public function assign_to_OAuthClients(): void
    {
        $createOAuthClient = OAuthClientCreate::create(
            externalId: md5('userdbaltest'),
            externalIdReadable: 'userdbaltest',
            name: 'UserDbalTest',
            displayName: 'UserDbalTest',
            secret: 'V3ery$ecr5t',
        );
        $this->entityManager->persist($createOAuthClient);
        $this->entityManager->flush();

        /** @var \Coddin\IdentityProvider\Entity\OpenIDConnect\User $user */
        $user = $this->repository->findOneByUsername(DataUser::UserName->value);

        $this->repository->assignToOAuthClients($user, $createOAuthClient);

        /** @var \Coddin\IdentityProvider\Entity\OpenIDConnect\User $user */
        $user = $this->repository->findOneByUsername(DataUser::UserName->value);
        /** @var \Coddin\IdentityProvider\Entity\OpenIDConnect\UserOAuthClient $userAssignedOauthClient */
        $userAssignedOauthClient = $user->getUserOAuthClients()[1];
        $oauthClient = $userAssignedOauthClient->getOAuthClient();

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(OAuthClient::class, $userAssignedOauthClient->getOAuthClient());
        self::assertEquals('userdbaltest', $oauthClient->getExternalIdReadable());
    }
}
