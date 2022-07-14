<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Repository\OAuthClientRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\ClientRepository
 * @covers ::__construct
 */
final class ClientRepositoryTest extends TestCase
{
    /** @var OAuthClientRepository & MockObject */
    private $oauthClientRepository;

    protected function setUp(): void
    {
        $this->oauthClientRepository = $this->createMock(OAuthClientRepository::class);
    }

    /**
     * @test
     * @covers ::getClientEntity
     */
    public function get_client_entity_not_found(): void
    {
        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('client_identifier')
            ->willThrowException(new OAuthEntityNotFoundException());

        $clientRepository = new \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\ClientRepository($this->oauthClientRepository);
        $clientEntity = $clientRepository->getClientEntity('client_identifier');

        self::assertNull($clientEntity);
    }

    /**
     * @test
     * @covers ::getClientEntity
     */
    public function get_client_entity(): void
    {
        $oauthClient = $this->createOAuthClient();

        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('client_identifier')
            ->willReturn($oauthClient);

        $clientRepository = new \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\ClientRepository($this->oauthClientRepository);
        $clientEntity = $clientRepository->getClientEntity('client_identifier');

        self::assertInstanceOf(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\ClientEntity::class, $clientEntity);
        self::assertEquals('client_identifier', $clientEntity->getIdentifier());
        self::assertEquals('Name', $clientEntity->getName());
        self::assertFalse($clientEntity->isConfidential());
    }

    /**
     * @test
     * @covers ::validateClient
     */
    public function validate_client_not_found(): void
    {
        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('client_identifier')
            ->willThrowException(new OAuthEntityNotFoundException());

        $clientRepository = new \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\ClientRepository($this->oauthClientRepository);
        $isValid = $clientRepository->validateClient(
            clientIdentifier: 'client_identifier',
            clientSecret: 'client_secret',
            grantType: null,
        );

        self::assertFalse($isValid);
    }

    /**
     * @test
     * @covers ::validateClient
     */
    public function validate_client_pkce(): void
    {
        $oauthClient = $this->createOAuthClient(true);

        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('client_identifier')
            ->willReturn($oauthClient);

        $clientRepository = new \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\ClientRepository($this->oauthClientRepository);
        $isValid = $clientRepository->validateClient(
            clientIdentifier: 'client_identifier',
            clientSecret: 'client_secret',
            grantType: null,
        );

        self::assertTrue($isValid);
    }

    /**
     * @test
     * @covers ::validateClient
     */
    public function validate_client_not_pkce_incorrect_secret(): void
    {
        $oauthClient = $this->createOAuthClient();

        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('client_identifier')
            ->willReturn($oauthClient);

        $clientRepository = new \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\ClientRepository($this->oauthClientRepository);
        $isValid = $clientRepository->validateClient(
            clientIdentifier: 'client_identifier',
            clientSecret: null,
            grantType: null,
        );

        self::assertFalse($isValid);
    }

    /**
     * @test
     * @covers ::validateClient
     */
    public function validate_client_not_pkce_confidential_secret_mismatch(): void
    {
        $oauthClient = $this->createOAuthClient(false, true);

        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('client_identifier')
            ->willReturn($oauthClient);

        $clientRepository = new \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\ClientRepository($this->oauthClientRepository);
        $isValid = $clientRepository->validateClient(
            clientIdentifier: 'client_identifier',
            clientSecret: 'thisisasecret',
            grantType: null,
        );

        self::assertFalse($isValid);
    }

    private function createOAuthClient(
        bool $pkce = false,
        bool $confidential = false,
    ): OAuthClient {
        return new OAuthClient(
            externalId: \md5('client_identifier'),
            externalIdReadable: 'client_identifier',
            name: 'Name',
            displayName: 'DisplayName',
            isConfidential: $confidential,
            isPkce: $pkce,
            secret: \password_hash('$ecr3t', PASSWORD_BCRYPT),
            creationWebhookUrl: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
            redirectUris: new ArrayCollection(),
            oAuthAuthorizationCodes: new ArrayCollection(),
            oAuthAccessTokens: new ArrayCollection(),
            userOAuthClients: new ArrayCollection(),
        );
    }
}
