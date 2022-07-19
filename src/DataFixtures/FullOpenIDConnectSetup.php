<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\DataFixtures;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAccessToken;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAuthorizationCode;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthRefreshToken;
use Coddin\IdentityProvider\Entity\OpenIDConnect\PasswordResetRequest;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Generator\OAuthClientCreate;
use Coddin\IdentityProvider\Generator\UserCreate;
use Coddin\IdentityProvider\Generator\UserOAuthClientCreate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class FullOpenIDConnectSetup extends Fixture
{
    private ObjectManager $objectManager;

    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;

        $oauthClient = $this->setupOAuthClient();
        $manager->persist($oauthClient);

        /** @noinspection PhpUnhandledExceptionInspection */
        $user = UserCreate::create(
            username: Data\User::UserName->value,
            email: Data\User::Email->value,
            password: Data\User::Password->value,
        );
        $manager->persist($user);

        $userOAuthClient = UserOAuthClientCreate::create(
            user: $user,
            oauthClient: $oauthClient,
        );
        $manager->persist($userOAuthClient);

        $oauthAccessToken = new OAuthAccessToken(
            externalId: Data\OAuthAccessToken::ExternalID->value,
            expiresAt: (new \DateTimeImmutable())->add(new \DateInterval('PT1H')),
            user: $user,
            oAuthClient: $oauthClient,
        );
        $manager->persist($oauthAccessToken);

        $refreshToken = new OAuthRefreshToken(
            externalId: Data\OAuthRefreshToken::EXTERNAL_ID->value,
            createdAt: new \DateTimeImmutable(),
            expiresAt: (new \DateTimeImmutable())->add(new \DateInterval('PT1H')),
            oAuthAccessToken: $oauthAccessToken,
        );
        $manager->persist($refreshToken);

        $this->authorizationCodeSetup($user, $oauthClient);

        $validPasswordResetRequest = new PasswordResetRequest(
            $user,
            Data\PasswordResetRequest::Token->value,
            new \DateTimeImmutable(),
            \DateTimeImmutable::createFromMutable((new \DateTime())->add(new \DateInterval('PT1H'))),
        );
        $manager->persist($validPasswordResetRequest);

        $invalidPasswordResetRequest = new PasswordResetRequest(
            $user,
            Data\PasswordResetRequest::InvalidToken->value,
            \DateTimeImmutable::createFromMutable((new \DateTime())->sub(new \DateInterval('PT23H'))),
            \DateTimeImmutable::createFromMutable((new \DateTime())->sub(new \DateInterval('PT22H'))),
        );
        $manager->persist($invalidPasswordResetRequest);

        $manager->flush();
    }

    private function setupOAuthClient(): OAuthClient
    {
        return OAuthClientCreate::create(
            externalId: Data\OAuthClient::ExternalID->value,
            externalIdReadable: Data\OAuthClient::ExternalIDReadable->value,
            name: Data\OAuthClient::Name->value,
            displayName: Data\OAuthClient::DisplayName->value,
            secret: Data\OAuthClient::Secret->value,
            isConfidential: false,
            isPkce: false,
        );
    }

    private function authorizationCodeSetup(
        User $user,
        OAuthClient $oauthClient,
    ): void {
        $validOauthAuthorizationCode = new OAuthAuthorizationCode(
            externalId: Data\OAuthAuthorizationCode::VALID_EXTERNAL_ID->value,
            redirectUri: 'redirect_uri',
            expiresAt: (new \DateTimeImmutable())->add(new \DateInterval('PT1H')),
            user: $user,
            oAuthClient: $oauthClient,
        );
        $this->objectManager->persist($validOauthAuthorizationCode);

        $expiredOauthAuthorizationCode = new OAuthAuthorizationCode(
            externalId: Data\OAuthAuthorizationCode::EXPIRED_EXTERNAL_ID->value,
            redirectUri: 'redirect_uri',
            expiresAt: (new \DateTimeImmutable())->sub(new \DateInterval('PT1H')),
            user: $user,
            oAuthClient: $oauthClient,
        );
        $this->objectManager->persist($expiredOauthAuthorizationCode);

        $revokedOauthAuthorizationCode = new OAuthAuthorizationCode(
            externalId: Data\OAuthAuthorizationCode::REVOKED_EXTERNAL_ID->value,
            redirectUri: 'redirect_uri',
            expiresAt: (new \DateTimeImmutable())->add(new \DateInterval('PT1H')),
            user: $user,
            oAuthClient: $oauthClient,
            revokedAt: (new \DateTimeImmutable())->sub(new \DateInterval('PT1H')),
        );
        $this->objectManager->persist($revokedOauthAuthorizationCode);
    }
}
