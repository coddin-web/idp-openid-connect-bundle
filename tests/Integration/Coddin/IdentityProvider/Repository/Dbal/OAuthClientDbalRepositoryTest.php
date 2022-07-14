<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace Tests\Integration\Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Collection\OAuthClientCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Repository\Dbal\OAuthClientDbalRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\Dbal\OAuthClientDbalRepository
 * @covers ::__construct
 */
final class OAuthClientDbalRepositoryTest extends KernelTestCase
{
    private OAuthClientDbalRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        /* @phpstan-ignore-next-line */
        $this->repository = self::$kernel->getContainer()
            ->get(OAuthClientDbalRepository::class);
    }

    /**
     * @test
     * @covers ::getOneByExternalId
     */
    public function get_one_by_external_id_not_found(): void
    {
        self::expectException(OAuthEntityNotFoundException::class);

        $this->repository->getOneByExternalId('incorrect_external_id');
    }

    /**
     * @test
     * @covers ::getOneByExternalId
     */
    public function get_one_by_external_id(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $oauthClient = $this->repository->getOneByExternalId(md5('company/client'));

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(OAuthClient::class, $oauthClient);
    }

    /**
     * @test
     * @covers ::getAll
     */
    public function get_all(): void
    {
        $oauthClients = $this->repository->getAll();

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(OAuthClientCollection::class, $oauthClients);
        self::assertCount(1, $oauthClients->all());
    }
}
