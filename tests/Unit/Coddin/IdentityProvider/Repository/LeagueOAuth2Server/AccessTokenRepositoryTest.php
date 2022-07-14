<?php

/**
 * @noinspection PhpMissingFieldTypeInspection
 * @noinspection PhpDocMissingThrowsInspection
 */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAccessToken;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Generator\OAuthAccessTokenCreate;
use Coddin\IdentityProvider\Repository\OAuthAccessTokenRepository;
use Coddin\IdentityProvider\Repository\OAuthClientRepository;
use Coddin\IdentityProvider\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\Factory\AccessTokenEntityFactory;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\ScopeEntity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\AccessTokenRepository
 * @covers ::__construct
 */
final class AccessTokenRepositoryTest extends TestCase
{
    /** @var EntityManagerInterface & MockObject $entityManager */
    private $entityManager;
    /** @var UserRepository & MockObject $userRepository */
    private $userRepository;
    /** @var OAuthClientRepository & MockObject $oauthClientRepository */
    private $oauthClientRepository;
    /** @var OAuthAccessTokenRepository & MockObject $oauthAccessTokenRepository */
    private $oauthAccessTokenRepository;
    /** @var AccessTokenEntityFactory & MockObject $accessTokenEntityFactory */
    private $accessTokenEntityFactory;
    /** @var OAuthAccessTokenCreate & MockObject $oauthAccessTokenCreate */
    private $oauthAccessTokenCreate;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->oauthClientRepository = $this->createMock(OAuthClientRepository::class);
        $this->oauthAccessTokenRepository = $this->createMock(OAuthAccessTokenRepository::class);
        $this->accessTokenEntityFactory = $this->createMock(AccessTokenEntityFactory::class);
        $this->oauthAccessTokenCreate = $this->createMock(OAuthAccessTokenCreate::class);
    }

    /**
     * @test
     * @covers ::getNewToken
     */
    public function get_new_token_incorrect_user_identifier(): void
    {
        $clientEntity = $this->createMock(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\ClientEntity::class);
        $scopeEntity = $this->createMock(ScopeEntity::class);

        $accessTokenEntity = $this->createMock(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\AccessTokenEntity::class);
        $accessTokenEntity
            ->expects(self::once())
            ->method('setClient')
            ->with($clientEntity);

        $accessTokenEntity
            ->expects(self::once())
            ->method('addScope')
            ->with($scopeEntity);

        $this->accessTokenEntityFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($accessTokenEntity);

        $accessTokenRepository = $this->setupAccessTokenRepository();

        $scopeEntityArray = [$scopeEntity];

        self::expectException(\LogicException::class);
        self::expectExceptionMessage('The userIdentifier is typed as mixed but should only be string|int|null');

        $accessTokenRepository->getNewToken(
            clientEntity: $clientEntity,
            scopes: $scopeEntityArray,
            userIdentifier: [],
        );
    }

    /**
     * @test
     * @covers ::getNewToken
     */
    public function get_new_token(): void
    {
        $clientEntity = $this->createMock(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\ClientEntity::class);
        $scopeEntity = $this->createMock(ScopeEntity::class);

        $accessTokenEntity = $this->createMock(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\AccessTokenEntity::class);
        $accessTokenEntity
            ->expects(self::once())
            ->method('setClient')
            ->with($clientEntity);

        $accessTokenEntity
            ->expects(self::once())
            ->method('addScope')
            ->with($scopeEntity);

        $accessTokenEntity
            ->expects(self::once())
            ->method('setUserIdentifier')
            ->with('identifier');

        $this->accessTokenEntityFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($accessTokenEntity);

        $accessTokenRepository = $this->setupAccessTokenRepository();

        $scopeEntityArray = [$scopeEntity];

        $accessToken = $accessTokenRepository->getNewToken(
            clientEntity: $clientEntity,
            scopes: $scopeEntityArray,
            userIdentifier: 'identifier',
        );

        self::assertInstanceOf(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\AccessTokenEntity::class, $accessToken);
    }

    /**
     * @test
     * @covers ::persistNewAccessToken
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function persist_new_accessToken_incorrect_user_identifier(): void
    {
        $accessTokenEntity = $this->createMock(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\AccessTokenEntity::class);
        $accessTokenEntity
            ->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn([]);

        $accessTokenRepository = $this->setupAccessTokenRepository();

        self::expectException(\LogicException::class);
        self::expectExceptionMessage(
            'Incorrect usage of the AccessTokenEntity, an identifier should always be a string',
        );

        $accessTokenRepository->persistNewAccessToken($accessTokenEntity);
    }

    /**
     * @test
     * @covers ::persistNewAccessToken
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function persist_new_accessToken(): void
    {
        $accessTokenEntity = $this->createMock(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\AccessTokenEntity::class);
        $accessTokenEntity
            ->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn('user_identifier');

        $user = $this->createMock(User::class);

        $this->userRepository
            ->expects(self::once())
            ->method('getOneByUuid')
            ->with('user_identifier')
            ->willReturn($user);

        $clientEntity = $this->createMock(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\ClientEntity::class);
        $clientEntity
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('uuid');

        $accessTokenEntity
            ->expects(self::once())
            ->method('getClient')
            ->willReturn($clientEntity);

        $oauthClient = $this->createMock(OAuthClient::class);

        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('uuid')
            ->willReturn($oauthClient);

        $accessTokenEntity
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('identifier');

        $dateTime = new \DateTimeImmutable();

        $accessTokenEntity
            ->expects(self::once())
            ->method('getExpiryDateTime')
            ->willReturn($dateTime);

        $oauthAccessToken = $this->createMock(OAuthAccessToken::class);

        $this->oauthAccessTokenCreate
            ->expects(self::once())
            ->method('create')
            ->with('identifier', $dateTime, $user, $oauthClient)
            ->willReturn($oauthAccessToken);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($oauthAccessToken);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $accessTokenRepository = $this->setupAccessTokenRepository();

        $accessTokenRepository->persistNewAccessToken($accessTokenEntity);
    }

    /**
     * @test
     * @covers ::revokeAccessToken
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function revoke_accessToken(): void
    {
        $accessToken = $this->createMock(OAuthAccessToken::class);

        $this->oauthAccessTokenRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('token_id')
            ->willReturn($accessToken);

        $this->oauthAccessTokenRepository
            ->expects(self::once())
            ->method('revoke')
            ->with($accessToken);

        $accessTokenRepository = $this->setupAccessTokenRepository();

        $accessTokenRepository->revokeAccessToken('token_id');
    }

    /**
     * @test
     * @dataProvider accessTokenRevokedData
     * @covers ::isAccessTokenRevoked
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function is_accessToken_revoked(?\DateTimeImmutable $revokedAt, bool $revoked): void
    {
        $accessToken = $this->createMock(OAuthAccessToken::class);
        $accessToken
            ->expects(self::once())
            ->method('getRevokedAt')
            ->willReturn($revokedAt);

        $this->oauthAccessTokenRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('token_id')
            ->willReturn($accessToken);

        $accessTokenRepository = $this->setupAccessTokenRepository();

        $isRevoked = $accessTokenRepository->isAccessTokenRevoked('token_id');

        self::assertEquals($revoked, $isRevoked);
    }

    /**
     * @return array<array{null|\DateTimeImmutable, bool}>
     */
    public function accessTokenRevokedData(): array
    {
        return [
            [null, false],
            [new \DateTimeImmutable(), true],
        ];
    }

    private function setupAccessTokenRepository(): \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\AccessTokenRepository
    {
        return new \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\AccessTokenRepository(
            entityManager: $this->entityManager,
            userRepository: $this->userRepository,
            oauthClientRepository: $this->oauthClientRepository,
            oauthAccessTokenRepository: $this->oauthAccessTokenRepository,
            accessTokenEntityFactory: $this->accessTokenEntityFactory,
            oauthAccessTokenCreate: $this->oauthAccessTokenCreate,
        );
    }
}
