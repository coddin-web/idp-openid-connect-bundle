<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAuthorizationCode;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use DateTimeImmutable;

final class OAuthAuthorizationCodeCreate
{
    public static function create(
        string $identifier,
        string $redirectUri,
        DateTimeImmutable $expiresAt,
        User $user,
        OAuthClient $oauthClient,
    ): OAuthAuthorizationCode {
        return new OAuthAuthorizationCode(
            externalId: $identifier,
            redirectUri: $redirectUri,
            expiresAt: $expiresAt,
            user: $user,
            oAuthClient: $oauthClient,
        );
    }
}
