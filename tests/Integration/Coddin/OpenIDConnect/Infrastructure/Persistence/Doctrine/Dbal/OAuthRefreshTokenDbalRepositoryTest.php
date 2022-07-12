<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace Tests\Integration\Coddin\OpenIDConnect\Infrastructure\Persistence\Doctrine\Dbal;

use Coddin\IdentityProvider\DataFixtures\Data\OAuthRefreshToken;
use Coddin\IdentityProvider\Repository\Dbal\OAuthRefreshTokenDbalRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\Dbal\OAuthRefreshTokenDbalRepository
 * @covers ::__construct
 */
final class OAuthRefreshTokenDbalRepositoryTest extends KernelTestCase
{
    private OAuthRefreshTokenDbalRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /* @phpstan-ignore-next-line */
        $this->repository = $kernel->getContainer()
            ->get(OAuthRefreshTokenDbalRepository::class);
        /* @phpstan-ignore-next-line */
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * @test
     * @covers ::getOneByExternalId
     */
    public function get_one_by_external_id_non_existing(): void
    {
        self::expectException(OAuthEntityNotFoundException::class);
        $this->repository->getOneByExternalId('non_existing_external_id');
    }

    /**
     * @test
     * @covers ::getOneByExternalId
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function get_one_by_external_id(): void
    {
        $refreshToken = $this->repository->getOneByExternalId(OAuthRefreshToken::EXTERNAL_ID->value);

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(\Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthRefreshToken::class, $refreshToken);
    }

    /**
     * @test
     * @covers ::revoke
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function revoke(): void
    {
        $refreshToken = $this->repository->getOneByExternalId(OAuthRefreshToken::EXTERNAL_ID->value);
        self::assertNull($refreshToken->getRevokedAt());

        $this->repository->revoke($refreshToken);
        $this->entityManager->refresh($refreshToken);

        $refreshToken = $this->repository->getOneByExternalId(OAuthRefreshToken::EXTERNAL_ID->value);
        self::assertInstanceOf(\DateTimeInterface::class, $refreshToken->getRevokedAt());
    }
}
