<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Generator\OAuthAccessTokenCreate;
use Coddin\IdentityProvider\Generator\OAuthRefreshTokenCreate;
use Coddin\IdentityProvider\Generator\UserCreate;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Generator\OAuthRefreshTokenCreate
 */
final class OAuthRefreshTokenCreateTest extends TestCase
{
    /**
     * @test
     * @covers ::create
     */
    public function create_the_refreshToken(): void
    {
        $roles = ['ROLE_ADMIN'];
        $password = 'thisisaverysecurepassword';

        $user = UserCreate::create(
            username: 'username',
            email: 'user@name.test',
            password: $password,
            roles: $roles,
        );

        $oauthClient = new OAuthClient(
            externalId: 'externalId',
            externalIdReadable: 'externalIdReadable',
            name: 'name',
            displayName: 'displayName',
            isConfidential: true,
            isPkce: true,
            secret: 'secret',
            creationWebhookUrl: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
            redirectUris: new ArrayCollection(),
            oAuthAuthorizationCodes: new ArrayCollection(),
            oAuthAccessTokens: new ArrayCollection(),
            userOAuthClients: new ArrayCollection(),
        );

        $oauthAccessTokenCreate = new OAuthAccessTokenCreate();
        $oauthAccessToken = $oauthAccessTokenCreate->create(
            externalId: 'externalId',
            expiresAt: new \DateTimeImmutable(),
            user: $user,
            oAuthClient: $oauthClient,
        );

        $oauthRefreshTokenCreate = new OAuthRefreshTokenCreate();
        $refreshToken = $oauthRefreshTokenCreate->create(
            externalId: 'externalId',
            expiresAt: new \DateTimeImmutable(),
            oauthAccessToken: $oauthAccessToken,
        );

        self::assertEquals($user->getUsername(), $refreshToken->getAccessToken()->getUser()->getUsername());
        self::assertEquals($oauthClient->getDisplayName(), $refreshToken->getAccessToken()->getOAuthClient()->getDisplayName());
    }
}
