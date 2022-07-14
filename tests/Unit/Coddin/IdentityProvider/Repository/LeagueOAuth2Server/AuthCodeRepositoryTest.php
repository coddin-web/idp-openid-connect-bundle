<?php

/**
 * @noinspection PhpMissingFieldTypeInspection
 * @noinspection PhpDocMissingThrowsInspection
 */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\AuthCodeEntity;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAuthorizationCode;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Generator\OAuthAuthorizationCodeCreate;
use Coddin\IdentityProvider\Repository\OAuthAuthorizationCodeRepository;
use Coddin\IdentityProvider\Repository\OAuthClientRepository;
use Coddin\IdentityProvider\Repository\UserRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\AuthCodeRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\AuthCodeRepository
 * @covers ::__construct
 */
final class AuthCodeRepositoryTest extends TestCase
{
    /** @var EntityManagerInterface & MockObject $entityManager */
    private $entityManager;
    /** @var UserRepository & MockObject $userRepository */
    private $userRepository;
    /** @var OAuthClientRepository & MockObject $oauthClientRepository */
    private $oauthClientRepository;
    /** @var OAuthAuthorizationCodeRepository & MockObject $oauthAuthorizationCodeRepository */
    private $oauthAuthorizationCodeRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->oauthClientRepository = $this->createMock(OAuthClientRepository::class);
        $this->oauthAuthorizationCodeRepository = $this->createMock(OAuthAuthorizationCodeRepository::class);
    }

    /**
     * @test
     * @covers ::persistNewAuthCode
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function persist_new_authCode_missing_identifier(): void
    {
        $authCodeEntity = $this->createMock(originalClassName: AuthCodeEntity::class);
        $authCodeEntity
            ->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn(null);

        self::expectException(\LogicException::class);
        self::expectExceptionMessage(
            'Incorrect usage of the AuthCodeEntity, an identifier should always be a string',
        );

        $authCodeRepository = $this->getAuthCodeRepository();
        $authCodeRepository->persistNewAuthCode(authCodeEntity: $authCodeEntity);
    }

    /**
     * @test
     * @covers ::persistNewAuthCode
     */
    public function persist_new_authCode_user_not_found(): void
    {
        $authCodeEntity = $this->createMock(originalClassName: AuthCodeEntity::class);
        $authCodeEntity
            ->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn('this_is_a_user_uuid');

        $this->userRepository
            ->expects(self::once())
            ->method('getOneByUuid')
            ->willThrowException(
                OAuthEntityNotFoundException::fromClassNameAndUuid(
                    className: User::class,
                    uuid: 'this_is_a_user_uuid',
                ),
            );

        self::expectException(OAuthEntityNotFoundException::class);
        self::expectExceptionMessage(
            'OAuthEntity of type `Coddin\IdentityProvider\Entity\OpenIDConnect\User` with uuid `this_is_a_user_uuid` was not found',
        );

        $authCodeRepository = $this->getAuthCodeRepository();
        $authCodeRepository->persistNewAuthCode(authCodeEntity: $authCodeEntity);
    }

    /**
     * @test
     * @covers ::persistNewAuthCode
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function persist_new_authCode_missing_redirectUri(): void
    {
        $authCodeEntity = $this->createMock(originalClassName: AuthCodeEntity::class);
        $authCodeEntity
            ->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn('this_is_a_user_uuid');

        $client = $this->createMock(ClientEntityInterface::class);
        $client
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('client_identifier');

        $user = $this->createMock(User::class);
        $this->userRepository
            ->expects(self::once())
            ->method('getOneByUuid')
            ->willReturn($user);

        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('client_identifier');

        $authCodeEntity
            ->expects(self::once())
            ->method('getClient')
            ->willReturn($client);

        self::expectException(\LogicException::class);
        self::expectExceptionMessage(
            'The redirectUri cannot be null',
        );

        $authCodeRepository = $this->getAuthCodeRepository();
        $authCodeRepository->persistNewAuthCode(authCodeEntity: $authCodeEntity);
    }

    /**
     * @test
     * @covers ::persistNewAuthCode
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function persist_new_authCode(): void
    {
        $authCodeEntity = $this->createMock(originalClassName: AuthCodeEntity::class);
        $authCodeEntity
            ->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn('this_is_a_user_uuid');

        $client = $this->createMock(ClientEntityInterface::class);
        $client
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('client_identifier');

        $user = $this->createMock(User::class);
        $this->userRepository
            ->expects(self::once())
            ->method('getOneByUuid')
            ->willReturn($user);

        $oauthClient = $this->createMock(OAuthClient::class);
        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('client_identifier')
            ->willReturn($oauthClient);

        $authCodeEntity
            ->expects(self::once())
            ->method('getClient')
            ->willReturn($client);
        $authCodeEntity
            ->expects(self::exactly(2))
            ->method('getRedirectUri')
            ->willReturn('https://foo.bar/callback');

        $authCodeEntity
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('identifier');
        $expiresAt = new \DateTimeImmutable();
        $authCodeEntity
            ->expects(self::once())
            ->method('getExpiryDateTime')
            ->willReturn($expiresAt);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(
                OAuthAuthorizationCodeCreate::create(
                    'identifier',
                    'https://foo.bar/callback',
                    $expiresAt,
                    $user,
                    $oauthClient,
                ),
            );
        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $authCodeRepository = $this->getAuthCodeRepository();
        $authCodeRepository->persistNewAuthCode(authCodeEntity: $authCodeEntity);
    }

    /**
     * @test
     * @dataProvider authCodeRevokedData
     * @covers ::isAuthCodeRevoked
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function is_AuthCode_revoked(
        OAuthAuthorizationCode|MockObject $authorizationCode,
        bool $result,
    ): void {
        $this->oauthAuthorizationCodeRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->willReturn($authorizationCode);

        $authCodeRepository = $this->getAuthCodeRepository();

        self::assertEquals($result, $authCodeRepository->isAuthCodeRevoked('code123'));
    }

    /**
     * @return array<int, array{OAuthAuthorizationCode, bool}>
     */
    public function authCodeRevokedData(): array
    {
        $revokedAuthorizationCode = $this->createMock(OAuthAuthorizationCode::class);
        $revokedAuthorizationCode
            ->expects(self::once())
            ->method('getRevokedAt')
            ->willReturn(new \DateTimeImmutable());

        $authorizationCode = $this->createMock(OAuthAuthorizationCode::class);
        $authorizationCode
            ->expects(self::once())
            ->method('getRevokedAt')
            ->willReturn(null);

        return [
            [$revokedAuthorizationCode, true],
            [$authorizationCode, false],
        ];
    }

    /**
     * @test
     * @covers ::revokeAuthCode
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function revoke_AuthCode(): void
    {
        $authorizationCode = $this->createMock(OAuthAuthorizationCode::class);
        $this->oauthAuthorizationCodeRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('code123')
            ->willReturn($authorizationCode);

        $this->oauthAuthorizationCodeRepository
            ->expects(self::once())
            ->method('revoke')
            ->with($authorizationCode);

        $authCodeRepository = $this->getAuthCodeRepository();
        $authCodeRepository->revokeAuthCode('code123');
    }

    /**
     * @test
     * @covers ::revokeAuthCode
     */
    public function revoke_AuthCode_not_found(): void
    {
        $this->oauthAuthorizationCodeRepository
            ->expects(self::once())
            ->method('getOneByExternalId')
            ->with('code123')
            ->willThrowException(new OAuthEntityNotFoundException());

        $this->oauthAuthorizationCodeRepository
            ->expects(self::never())
            ->method('revoke');

        self::expectException(\Exception::class);
        self::expectExceptionMessage('Failed to revoke authorization code');

        $authCodeRepository = $this->getAuthCodeRepository();
        $authCodeRepository->revokeAuthCode('code123');
    }

    /**
     * @test
     * @covers ::getNewAuthCode
     */
    public function get_new_AuthCode(): void
    {
        $authCodeRepository = $this->getAuthCodeRepository();
        $authCodeEntity = $authCodeRepository->getNewAuthCode();

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(AuthCodeEntity::class, $authCodeEntity);
    }

    private function getAuthCodeRepository(): AuthCodeRepository
    {
        return new AuthCodeRepository(
            entityManager: $this->entityManager,
            userRepository: $this->userRepository,
            oauthClientRepository: $this->oauthClientRepository,
            oauthAuthorizationCodeRepository: $this->oauthAuthorizationCodeRepository,
        );
    }
}
