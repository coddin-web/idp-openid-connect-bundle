<?php

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpMissingFieldTypeInspection
 */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Service\OpenIDConnect;

use Coddin\IdentityProvider\Repository\UserOAuthClientRepository;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\ClientEntity;
use Coddin\IdentityProvider\Service\OpenIDConnect\UserOAuthClientVerifier;
use League\OAuth2\Server\Entities\UserEntityInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Service\OpenIDConnect\UserOAuthClientVerifier
 * @covers ::__construct
 */
final class UserOAuthClientVerifierTest extends TestCase
{
    /** @var UserOAuthClientRepository & MockObject */
    private $userOAuthClientRepository;

    protected function setUp(): void
    {
        $this->userOAuthClientRepository = $this->createMock(UserOAuthClientRepository::class);
    }

    /**
     * @test
     * @covers ::verify
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function verify_incorrect_client_identifier(): void
    {
        $userOAuthClientVerifier = new UserOAuthClientVerifier($this->userOAuthClientRepository);

        $clientEntity = $this->createMock(ClientEntity::class);
        $userEntity = $this->createMock(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\UserEntity::class);

        $clientEntity
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn(null);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The Client Identifier must always be a string');

        $userOAuthClientVerifier->verify(
            $clientEntity,
            $userEntity,
        );
    }

    /**
     * @test
     * @covers ::verify
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function verify_incorrect_user_identifier(): void
    {
        $userOAuthClientVerifier = new UserOAuthClientVerifier($this->userOAuthClientRepository);

        $clientEntity = $this->createMock(ClientEntity::class);
        $userEntity = $this->createMock(UserEntityInterface::class);

        $clientEntity
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('client_identifier');

        $userEntity
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn(null);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The User Identifier must always be a string');

        $userOAuthClientVerifier->verify(
            $clientEntity,
            $userEntity,
        );
    }

    /**
     * @test
     * @covers ::verify
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function run_verify_successfully(): void
    {
        $userOAuthClientVerifier = new UserOAuthClientVerifier($this->userOAuthClientRepository);

        $clientEntity = $this->createMock(ClientEntity::class);
        $userEntity = $this->createMock(UserEntityInterface::class);

        $clientEntity
            ->expects(self::exactly(2))
            ->method('getIdentifier')
            ->willReturn('client_identifier');

        $userEntity
            ->expects(self::exactly(2))
            ->method('getIdentifier')
            ->willReturn('user_identifier');

        $this->userOAuthClientRepository
            ->expects(self::once())
            ->method('getOneByUserReferenceAndExternalId')
            ->with('user_identifier', 'client_identifier');

        $userOAuthClientVerifier->verify(
            $clientEntity,
            $userEntity,
        );
    }
}
