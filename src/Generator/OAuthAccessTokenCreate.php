<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAccessToken;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;

final class OAuthAccessTokenCreate
{
    public function create(
        string $externalId,
        \DateTimeImmutable $expiresAt,
        User $user,
        OAuthClient $oAuthClient,
    ): OAuthAccessToken {
        return new OAuthAccessToken(
            externalId: $externalId,
            expiresAt: $expiresAt,
            user: $user,
            oAuthClient: $oAuthClient,
        );
    }
}
