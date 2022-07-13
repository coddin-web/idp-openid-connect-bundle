<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\OpenIDConnect\Domain\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Generator\OAuthAuthorizationCodeCreate;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Generator\OAuthAuthorizationCodeCreate
 */
final class OAuthAuthorizationCodeCreateTest extends TestCase
{
    /**
     * @test
     * @covers ::create
     */
    public function create_the_authorization_code(): void
    {
        $identifier = '1234-5678-90ab';
        $redirectUri = 'https://unit.test.dev';
        $expiresAt = new \DateTimeImmutable();
        $user = $this->createMock(User::class);
        $oauthClient = $this->createMock(OAuthClient::class);

        $oauthAuthorizationCode = OAuthAuthorizationCodeCreate::create(
            identifier: $identifier,
            redirectUri: $redirectUri,
            expiresAt: $expiresAt,
            user: $user,
            oauthClient: $oauthClient,
        );

        self::assertEquals(
            $identifier,
            $oauthAuthorizationCode->getExternalId(),
        );

        self::assertEquals(
            $expiresAt,
            $oauthAuthorizationCode->getExpiresAt(),
        );
    }
}
