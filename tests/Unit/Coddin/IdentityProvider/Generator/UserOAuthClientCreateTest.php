<?php

/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PhpDocMissingThrowsInspection
 */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Generator\OAuthClientCreate;
use Coddin\IdentityProvider\Generator\UserCreate;
use Coddin\IdentityProvider\Generator\UserOAuthClientCreate;
use Coddin\IdentityProvider\DataFixtures\Data\OAuthClient as OAuthClientData;
use Tests\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Generator\UserOAuthClientCreate
 */
final class UserOAuthClientCreateTest extends TestCase
{
    /**
     * @test
     * @covers ::create
     */
    public function create_the_userOauthClient(): void
    {
        $roles = ['ROLE_ADMIN'];
        $password = 'thisisaverysecurepassword';

        $user = UserCreate::create(
            username: 'username',
            email: 'user@name.test',
            password: $password,
            roles: $roles,
        );

        $oauthClient = OAuthClientCreate::create(
            externalId: OAuthClientData::ExternalID->value,
            externalIdReadable: OAuthClientData::ExternalIDReadable->value,
            name: OAuthClientData::Name->value,
            displayName: OAuthClientData::DisplayName->value,
            secret: OAuthClientData::Secret->value,
        );

        $userOAuthClient = UserOAuthClientCreate::create(
            user: $user,
            oauthClient: $oauthClient,
        );

        self::assertVariableEqualsGetMethod(
            $userOAuthClient->getUser(),
            [
                'username' => $user->getUsername(),
                'password' => $user->getPassword(),
            ],
        );

        self::assertVariableEqualsGetMethod(
            $userOAuthClient->getOAuthClient(),
            [
                'externalId' => $oauthClient->getExternalId(),
                'externalIdReadable' => $oauthClient->getExternalIdReadable(),
                'name' => $oauthClient->getName(),
                'displayName' => $oauthClient->getDisplayName(),
                'secret' => $oauthClient->getSecret(),
            ],
        );
    }
}
