<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace Tests\Integration\Coddin\OpenIDConnect\Infrastructure\Persistence\Doctrine\Dbal;

use Coddin\IdentityProvider\DataFixtures\Data\OAuthAccessToken;
use Coddin\IdentityProvider\Repository\Dbal\OAuthAccessTokenDbalRepository;
use Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\Dbal\OAuthAccessTokenDbalRepository
 */
final class OAuthAccessTokenDbalRepositoryTest extends KernelTestCase
{
    private OAuthAccessTokenDbalRepository $repository;
    private UserDbalRepository $userRepository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /* @phpstan-ignore-next-line */
        $this->repository = $kernel->getContainer()
            ->get(OAuthAccessTokenDbalRepository::class);
        /* @phpstan-ignore-next-line */
        $this->userRepository = $kernel->getContainer()
            ->get(UserDbalRepository::class);
        /* @phpstan-ignore-next-line */
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * @test
     * @covers ::getOneByExternalId
     */
    public function get_one_by_external_id_not_found(): void
    {
        self::expectException(OAuthEntityNotFoundException::class);
        self::expectExceptionMessage(
            message: 'OAuthEntity of type `Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAccessToken` with externalId `incorrect_access_token_id` was not found', // phpcs:ignore
        );

        $this->repository->getOneByExternalId('incorrect_access_token_id');
    }

    /**
     * @test
     * @covers ::getOneByExternalId
     */
    public function get_one_by_external_id(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $accessToken = $this->repository->getOneByExternalId(
            externalId: OAuthAccessToken::ExternalID->value,
        );

        self::assertEquals('username', $accessToken->getUser()->getUsername());
        self::assertEquals('company/client', $accessToken->getOAuthClient()->getExternalIdReadable());
        self::assertNull($accessToken->getRevokedAt());
    }

    /**
     * @test
     * @covers ::findAllActiveForUser
     */
    public function find_all_active_for_user(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $user = $this->userRepository->getOneById(1);

        $accessTokenCollection = $this->repository->findAllActiveForUser($user);

        self::assertCount(1, $accessTokenCollection->all());
    }

    /**
     * @test
     * @covers ::revoke
     */
    public function revoke(): void
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $accessToken = $this->repository->getOneByExternalId(
            externalId: OAuthAccessToken::ExternalID->value,
        );
        $this->repository->revoke($accessToken);
        $this->entityManager->refresh($accessToken);

        /** @noinspection PhpUnhandledExceptionInspection */
        self::assertInstanceOf(
            expected: \DateTimeInterface::class,
            actual: $this->repository->getOneByExternalId(OAuthAccessToken::ExternalID->value)->getRevokedAt(),
        );
    }

    /**
     * @test
     * @covers ::revokeAllActiveForUser
     */
    public function revoke_all_active_for_user(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $user = $this->userRepository->getOneById(1);

        $this->repository->revokeAllActiveForUser($user);

        $accessTokenCollection = $this->repository->findAllActiveForUser($user);
        self::assertCount(0, $accessTokenCollection->all());
    }
}
