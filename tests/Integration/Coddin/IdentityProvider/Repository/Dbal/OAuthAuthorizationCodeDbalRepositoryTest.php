<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace Tests\Integration\Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Collection\OAuthAuthorizationCodeCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAuthorizationCode;
use Coddin\IdentityProvider\Repository\Dbal\OAuthAuthorizationCodeDbalRepository;
use Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\Dbal\OAuthAuthorizationCodeDbalRepository
 * @covers ::__construct
 */
final class OAuthAuthorizationCodeDbalRepositoryTest extends KernelTestCase
{
    private OAuthAuthorizationCodeDbalRepository $repository;

    public function setUp(): void
    {
        self::bootKernel();

        /* @phpstan-ignore-next-line */
        $this->repository = self::$kernel->getContainer()
            ->get(OAuthAuthorizationCodeDbalRepository::class);
    }

    /**
     * @test
     * @covers ::getOneByExternalId
     */
    public function get_one_by_external_id_not_found(): void
    {
        self::expectException(OAuthEntityNotFoundException::class);

        $this->repository->getOneByExternalId('incorrect_authorization_code_external_id');
    }

    /**
     * @test
     * @covers ::getOneByExternalId
     */
    public function get_one_by_external_id(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $oauthAuthorizationCode = $this->repository->getOneByExternalId('authorization_code_external_id');

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(OAuthAuthorizationCode::class, $oauthAuthorizationCode);
    }

    /**
     * @test
     * @covers ::findAllActiveForUser
     */
    public function find_all_active_for_user(): void
    {
        /** @var UserDbalRepository $userRepository */
        $userRepository = self::$kernel->getContainer()
            ->get(UserDbalRepository::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $user = $userRepository->getOneById(1);

        $allActiveAuthorizationCodes = $this->repository->findAllActiveForUser($user);
        $allAuthorizationCodes = $this->repository->findAll();

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(OAuthAuthorizationCodeCollection::class, $allActiveAuthorizationCodes);

        self::assertCount(3, $allAuthorizationCodes);
        self::assertCount(1, $allActiveAuthorizationCodes->all());
    }

    /**
     * @test
     * @covers ::revoke
     */
    public function revoke(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $activeAuthorizationCode = $this->repository->getOneByExternalId('authorization_code_external_id');

        $this->repository->revoke($activeAuthorizationCode);

        /** @var UserDbalRepository $userRepository */
        $userRepository = self::$kernel->getContainer()
            ->get(UserDbalRepository::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $user = $userRepository->getOneById(1);

        $allActiveAuthorizationCodes = $this->repository->findAllActiveForUser($user);
        $allAuthorizationCodes = $this->repository->findAll();

        self::assertCount(3, $allAuthorizationCodes);
        self::assertCount(0, $allActiveAuthorizationCodes->all());
    }

    /**
     * @test
     * @covers ::revokeAllActiveForUser
     */
    public function revoke_all_active_for_user(): void
    {
        /** @var UserDbalRepository $userRepository */
        $userRepository = self::$kernel->getContainer()
            ->get(UserDbalRepository::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $user = $userRepository->getOneById(1);

        $this->repository->revokeAllActiveForUser($user);

        $allActiveAuthorizationCodes = $this->repository->findAllActiveForUser($user);
        $allAuthorizationCodes = $this->repository->findAll();

        self::assertCount(3, $allAuthorizationCodes);
        self::assertCount(0, $allActiveAuthorizationCodes->all());
    }
}
