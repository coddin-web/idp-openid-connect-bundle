<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace Tests\Integration\Coddin\OpenIDConnect\Infrastructure\Persistence\Doctrine\Dbal;

use Coddin\IdentityProvider\DataFixtures\Data\OAuthClient;
use Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository;
use Coddin\IdentityProvider\Repository\Dbal\UserOAuthClientDbalRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\Dbal\UserOAuthClientDbalRepository
 * @covers ::__construct
 */
final class UserOAuthClientDbalRepositoryTest extends KernelTestCase
{
    private UserOAuthClientDbalRepository $repository;
    private UserDbalRepository $userRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /* @phpstan-ignore-next-line */
        $this->repository = $kernel->getContainer()
            ->get(UserOAuthClientDbalRepository::class);
        /* @phpstan-ignore-next-line */
        $this->userRepository = $kernel->getContainer()
            ->get(UserDbalRepository::class);
    }

    /**
     * @test
     * @covers ::getOneByUserReferenceAndExternalId
     */
    public function get_one_by_userReference_and_externalId_non_existing(): void
    {
        self::expectException(OAuthEntityNotFoundException::class);

        $this->repository->getOneByUserReferenceAndExternalId(
            userReference: 'user_reference_non_existing',
            externalId: 'external_id_non_existing',
        );
    }

    /**
     * @test
     * @covers ::getOneByUserReferenceAndExternalId
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function get_one_by_userReference_and_externalId(): void
    {
        $user = $this->userRepository->getOneById(1);
        $userReference = $user->getUuid();

        $userOAuthClient = $this->repository->getOneByUserReferenceAndExternalId(
            userReference: $userReference,
            externalId: OAuthClient::ExternalID->value,
        );

        self::assertEquals(
            expected: OAuthClient::DisplayName->value,
            actual: $userOAuthClient->getOAuthClient()->getDisplayName(),
        );
    }
}
