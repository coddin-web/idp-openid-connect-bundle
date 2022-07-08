<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAccessToken;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthRefreshToken;

final class OAuthRefreshTokenCreate
{
    public function create(
        string $externalId,
        \DateTimeImmutable $expiresAt,
        OAuthAccessToken $oauthAccessToken,
        ?\DateTimeImmutable $revokedAt = null,
    ): OAuthRefreshToken {
        return new OAuthRefreshToken(
            externalId: $externalId,
            createdAt: new \DateTimeImmutable(),
            expiresAt: $expiresAt,
            oAuthAccessToken: $oauthAccessToken,
            revokedAt: $revokedAt,
        );
    }
}
