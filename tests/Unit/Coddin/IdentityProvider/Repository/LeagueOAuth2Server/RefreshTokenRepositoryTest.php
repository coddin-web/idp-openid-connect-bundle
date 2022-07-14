<?php

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpMissingFieldTypeInspection
 */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAccessToken;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthRefreshToken;
use Coddin\IdentityProvider\Generator\OAuthRefreshTokenCreate;
use Coddin\IdentityProvider\Repository\OAuthAccessTokenRepository;
use Coddin\IdentityProvider\Repository\OAuthRefreshTokenRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\AccessTokenEntity;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\RefreshTokenRepository
 * @covers ::__construct
 */
final class RefreshTokenRepositoryTest extends TestCase
{
    /** @var EntityManagerInterface & MockObject */
    private $entityManager;
    /** @var OAuthRefreshTokenCreate & MockObject */
    private $refreshTokenCreate;
    /** @var OAuthAccessTokenRepository & MockObject */
    private $oauthAccessTokenRepository;
    /** @var OAuthRefreshTokenRepository & MockObject */
    private $oauthRefreshTokenRepository;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->refreshTokenCreate = $this->createMock(OAuthRefreshTokenCreate::class);
        $this->oauthAccessTokenRepository = $this->createMock(OAuthAccessTokenRepository::class);
        $this->oauthRefreshTokenRepository = $this->createMock(OAuthRefreshTokenRepository::class);
    }

    /**
     * @test
     * @covers ::persistNewRefreshToken
     */
    public function persist_new_refresh_token_missing_access_token(): void
    {
        $this->oauthAccessTokenRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->willThrowException(new OAuthEntityNotFoundException());

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $accessToken = $this->createMock(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\AccessTokenEntity::class);
        $accessToken
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('foo.bar');

        $refreshTokenEntity = $this->createMock(RefreshTokenEntityInterface::class);
        $refreshTokenEntity
            ->expects(self::once())
            ->method('getAccessToken')
            ->willReturn($accessToken);

        self::expectException(OAuthEntityNotFoundException::class);

        $refreshTokenRepo = $this->createRepository();
        /** @noinspection PhpUnhandledExceptionInspection */
        $refreshTokenRepo->persistNewRefreshToken($refreshTokenEntity);
    }

    /**
     * @test
     * @covers ::persistNewRefreshToken
     */
    public function persist_new_refresh_token(): void
    {
        $oauthAccessToken = $this->createMock(OAuthAccessToken::class);
        $this->oauthAccessTokenRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->willReturn($oauthAccessToken);

        $accessToken = $this->createMock(AccessTokenEntity::class);
        $accessToken
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('foo.bar');

        $refreshTokenEntity = $this->createMock(RefreshTokenEntityInterface::class);
        $refreshTokenEntity
            ->expects(self::once())
            ->method('getAccessToken')
            ->willReturn($accessToken);
        $refreshTokenEntity
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('bar.foo');
        $refreshTokenEntity
            ->expects(self::once())
            ->method('getExpiryDateTime')
            ->willReturn(new \DateTimeImmutable());

        $refreshToken = $this->createMock(OAuthRefreshToken::class);
        $this->refreshTokenCreate
            ->expects(self::once())
            ->method('create')
            ->willReturn($refreshToken);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($refreshToken);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $refreshTokenRepo = $this->createRepository();
        /** @noinspection PhpUnhandledExceptionInspection */
        $refreshTokenRepo->persistNewRefreshToken($refreshTokenEntity);
    }

    /**
     * @test
     * @covers ::revokeRefreshToken
     */
    public function revoke_refresh_token_not_found(): void
    {
        $this->oauthRefreshTokenRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('token_id')
            ->willThrowException(new OAuthEntityNotFoundException());

        self::expectException(OAuthEntityNotFoundException::class);

        $refreshTokenRepo = $this->createRepository();
        $refreshTokenRepo->revokeRefreshToken('token_id');
    }

    /**
     * @test
     * @covers ::revokeRefreshToken
     */
    public function revoke_refresh_token(): void
    {
        $refreshToken = $this->createMock(OAuthRefreshToken::class);

        $this->oauthRefreshTokenRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('token_id')
            ->willReturn($refreshToken);

        $this->oauthRefreshTokenRepository
            ->expects(self::once())
            ->method('revoke')
            ->with($refreshToken);

        $refreshTokenRepo = $this->createRepository();
        /** @noinspection PhpUnhandledExceptionInspection */
        $refreshTokenRepo->revokeRefreshToken('token_id');
    }

    /**
     * @test
     * @covers ::isRefreshTokenRevoked
     */
    public function is_refresh_token_revoked_not_found(): void
    {
        $this->oauthRefreshTokenRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('token_id')
            ->willThrowException(new OAuthEntityNotFoundException());

        self::expectException(OAuthEntityNotFoundException::class);

        $refreshTokenRepo = $this->createRepository();
        $refreshTokenRepo->isRefreshTokenRevoked('token_id');
    }

    /**
     * @test
     * @covers ::isRefreshTokenRevoked
     * @dataProvider refreshTokenRevokedData
     */
    public function is_refresh_token_revoked(?\DateTimeImmutable $revoked, bool $isRevoked): void
    {
        $refreshToken = $this->createMock(OAuthRefreshToken::class);
        $refreshToken
            ->expects(self::once())
            ->method('getRevokedAt')
            ->willReturn($revoked);

        $this->oauthRefreshTokenRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('token_id')
            ->willReturn($refreshToken);

        $refreshTokenRepo = $this->createRepository();
        /** @noinspection PhpUnhandledExceptionInspection */
        $isRefreshTokenRevoked = $refreshTokenRepo->isRefreshTokenRevoked('token_id');

        self::assertEquals($isRevoked, $isRefreshTokenRevoked);
    }

    /**
     * @return array<array{null|\DateTimeImmutable, bool}>
     */
    public function refreshTokenRevokedData(): array
    {
        return [
            [null, false],
            [new \DateTimeImmutable(), true],
        ];
    }

    private function createRepository(): \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\RefreshTokenRepository
    {
        return new \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\RefreshTokenRepository(
            entityManager: $this->entityManager,
            oauthRefreshTokenCreate: $this->refreshTokenCreate,
            oauthAccessTokenRepository: $this->oauthAccessTokenRepository,
            oauthRefreshTokenRepository: $this->oauthRefreshTokenRepository,
        );
    }
}
